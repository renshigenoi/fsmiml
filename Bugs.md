# Laporan Bug — FSM Tech-IML

> **Audit Ronde 1: 2026-09-15 — SELESAI** · **Audit Ronde 2: 2026-09-15 (malam) — diperbaiki 2026-09-16**
>
> | Ronde | ✅ Selesai | ⚠️ Sebagian | ❌ Belum |
> |---|---|---|---|
> | Ronde 1 (#1–#42) | **42** | 0 | 0 |
> | Ronde 2 (A/B/C/D) | **30** | 0 | **0** |
>
> **Catatan:**
> - Ronde 1: Bug #1 sempat memicu regresi (`throttle:web` tak terdaftar → dashboard 500), sudah diperbaiki + diverifikasi. Sisa #12/#15/#16/#18/#29/#38 + cap sync dikerjakan malam harinya.
> - Ronde 2 adalah hasil audit ulang 3 auditor (HTTP layer, services, mobile). **Beberapa temuan Ronde 2 adalah regresi dari perbaikan Ronde 1** (A1 dari #24, A2 dari #4+#19, B10 dari #38) — kini sudah dirapikan.
> - Ronde 2 dieksekusi 2026-09-16: `php artisan test` → **45 passed**, `npx vite build` → sukses. Keputusan produk (A2 & B15) sudah diterapkan: realtime tracking kembali via public Channel berbasis token (A2), dan transisi `accepted`/`on_the_way → failed` ditambahkan (B15).

**Legenda:** ✅ [SELESAI] · ⚠️ [SEBAGIAN] · ❌ [BELUM]

---

## KRITIS (Wajib segera diperbaiki)

### 1. Web Routes & DashboardController Tanpa Authorization Peran — ✅ [SELESAI]
File: routes/web.php:23-48, app/Http/Controllers/Web/DashboardController.php:45-429
Bug: Seluruh route dashboard hanya pakai middleware auth, tanpa role check. Method storeWorkOrder(), updateWorkOrder(), showWorkOrder(), technicians(), searchSales() di DashboardController tidak ada authorization sama sekali. Teknisi yang login via web bisa membuat/mengubah work order dan melihat semua data.
Dampak: Melanggar FR-AUTH-003 & FR-AUTH-004. Akses tidak berwenang ke seluruh fitur admin.
**Status:** Diperbaiki — semua method DashboardController kini memanggil `$this->ensureCoordinatorAccess();` (abort 403 kecuali role Administrator/Coordinator, DashboardController.php:559-565), dan login web juga membatasi role (Web/AuthController.php:32-36). ⚠️ Perbaikan ini sempat menimbulkan **regresi**: `throttle:web` dipakai di routes/web.php:23 tapi limiternya tidak terdaftar → semua halaman dashboard error 500. Regresi sudah diperbaiki 2026-09-15 (limiter `web` didaftarkan di AppServiceProvider.php:54, 400/menit per user). Terverifikasi: `/dashboard` → 200 (coordinator), 403 (teknisi).

### 2. LeaveRequest::$fillable Tidak Lengkap — Approval Cuti Tidak Tersimpan — ✅ [SELESAI]
File: app/Modules/Attendance/Models/LeaveRequest.php:11
Bug: $fillable tidak menyertakan reviewed_by dan review_note. Saat AttendanceAdminController::reviewLeave() memanggil $leaveRequest->update([...]), field reviewed_by dan review_note diam-diam gagal tersimpan (mass-assignment blocked).
Dampak: Approval cuti tidak mencatat siapa reviewer dan catatan review hilang.
**Status:** Diperbaiki — `reviewed_by` dan `review_note` sudah masuk $fillable (LeaveRequest.php:11); reviewLeave() juga mengisi `reviewed_at`.

### 3. token_plain_encrypted Tidak Ada di $fillable — Token Tidak Pernah Tersimpan — ✅ [SELESAI]
File: app/Modules/Tracking/Models/TrackingToken.php:13-19
Bug: Kolom token_plain_encrypted ada di migration tetapi tidak di $fillable. Setiap TrackingToken::create() mengabaikan field ini, sehingga ensurePlaintextLink() selalu membuat token baru → penumpukan token aktif.
Dampak: Setiap akses dashboard menghasilkan token baru, memperluas surface area keamanan.
**Status:** Diperbaiki — `token_plain_encrypted` masuk $fillable (TrackingToken.php:16), dan `ensurePlaintextLink()` kini me-reuse token aktif yang sudah punya plaintext sebelum membuat baru (TrackingTokenService.php:39-58).

### 4. Public Broadcast Channel Tanpa Authorization — ✅ [SELESAI]
File: app/Modules/Tracking/Events/TrackingLocationUpdated.php:30-31, routes/channels.php
Bug: Event broadcast ke public Channel("tracking.{realtimeChannel}"), bukan PrivateChannel. Tidak ada authorization callback di channels.php. Siapa saja yang tahu nama channel (diekspos via endpoint publik) bisa mendengarkan lokasi teknisi.
Dampak: Melanggar NFR-SEC-005 & aturan "broadcast hanya ke channel yang diotorisasi."
**Status:** Diperbaiki — `broadcastOn()` kini return `PrivateChannel("work-order.{id}")` + `PrivateChannel("tracking.{channel}")` (TrackingLocationUpdated.php:26-35), dengan authorization callback untuk keduanya di routes/channels.php:16-30 (cek `$user->can('view', $workOrder)` / teknisi yang di-assign + status aktif).

### 5. LegacyController Tidak Meneruskan Data yang Divalidasi ke Service — ✅ [SELESAI]
File: app/Http/Controllers/Api/V1/LegacyController.php:54-60
Bug: StoreLegacyWorkOrderRequest memvalidasi customer_phone, latitude, longitude, location_address, customer_email sebagai required, tetapi controller tidak meneruskan field-field ini ke createFromSales(). Semua default ke null.
Dampak: Customer phone selalu null (notifikasi WhatsApp gagal), koordinat yang di-pin user di peta tidak digunakan.
**Status:** Diperbaiki — controller kini meneruskan kelima field hasil validasi ke `createFromSales()` (LegacyController.php:60-71).

### 6. PIN Disimpan sebagai Plaintext di localStorage — ✅ [SELESAI]
File: mobile/src/App.vue:2344, 2431, 2461
Bug: PIN 6-digit disimpan sebagai string plaintext di localStorage. Method hashPin() dan randomSalt() ada tetapi tidak pernah dipakai.
Dampak: Siapa saja dengan akses ke localStorage bisa melihat PIN.
**Status:** Diperbaiki — `saveLocalPin()` kini menyimpan `salt + ':' + hashPin(pin, salt)` (SHA-256 via crypto.subtle, fallback djb2); `hashPin()`/`randomSalt()` sudah dipakai. Ada cleanup key lama (`cleanupOldPinKeys()`). Catatan minor: setelah verifikasi server sukses, nilai disimpan sebagai marker `'verified'` (hash tidak pernah dipakai untuk autentifikasi — bukan isu keamanan).

### 7. verifyPin() Menerima PIN Lokal sebagai Fallback — Bypass Server — ✅ [SELESAI]
File: mobile/src/App.vue:2450-2453
Bug: Jika serverPinCheck return 'error' (jaringan gagal), user tetap bisa unlock jika PIN lokal cocok. User yang sudah logout/reset PIN di server tetap bisa masuk.
**Status:** Diperbaiki — status `'error'` kini hard-fail ("Tidak dapat verifikasi — periksa koneksi."); tidak ada lagi jalur yang membandingkan PIN lokal untuk unlock. Unlock hanya terjadi setelah server return 'ok'.

---

## TINGGI (Prioritas tinggi)

### 8. fail() Endpoint Tanpa Authorization Policy — ✅ [SELESAI]
File: app/Http/Controllers/Api/V1/WorkOrderController.php:216-219, app/Policies/WorkOrderPolicy.php
Bug: cancel() punya authorize('cancel'), tetapi fail() tidak ada policy check eksplisit — hanya authorize('view') yang terlalu longgar. Teknisi dengan assignment Pending/Superseded bisa lolos.
Dampak: Inconsistent authorization, potensi akses tidak sah.
**Status:** Diperbaiki — `fail()` memanggil `$this->authorize('fail', $workOrder)` (WorkOrderController.php:218) dengan `WorkOrderPolicy::fail()` yang hanya mengizinkan coordinator atau teknisi assigned berstatus accepted; TransitionService juga re-authorize.

### 9. PIN Login Tidak Validasi Role — Semua Role Bisa Login via PIN — ✅ [SELESAI]
File: app/Http/Controllers/Api/V1/AuthController.php:88-103
Bug: pinLogin() hanya cek email + PIN, tanpa validasi role. Padahal PIN login dirancang khusus untuk teknisi mobile. Administrator/coordinator bisa login via PIN.
Dampak: Akses tidak sah jika PIN bocor untuk akun admin.
**Status:** Diperbaiki — pinLogin() menolak role selain Technician dengan 422 "PIN login hanya untuk teknisi." (AuthController.php:97-99).

### 10. ProfileController — Ubah Password Tanpa Verifikasi Password Lama — ✅ [SELESAI]
File: app/Http/Controllers/Web/ProfileController.php:18-34
Bug: Password bisa diubah tanpa memverifikasi current_password. Bandingkan dengan API ChangePasswordRequest yang memverifikasi.
Dampak: Jika session disusupi, password bisa diubah tanpa resistance.
**Status:** Diperbaiki — kini pakai `UpdateProfileRequest` yang mewajibkan `current_password` saat password diisi dan memverifikasinya via `Hash::check` (UpdateProfileRequest.php:23, 28-35).

### 11. Web Login Tanpa Rate Limiting — ✅ [SELESAI]
File: routes/web.php:18-21
Bug: POST /login (web) tidak ada throttle:login, sedangkan API login punya. Brute force login web tidak dibatasi.
**Status:** Diperbaiki — POST /login memakai `throttle:login` (5/menit per email+IP, AppServiceProvider.php:51). API login & pinLogin juga pakai limiter yang sama.

### 12. Tidak Ada Idempotency untuk startTrip/arrive/cancel/fail — ✅ [SELESAI]
File: app/Http/Controllers/Api/V1/WorkOrderController.php:120-128, 209-218
Bug: Hanya startInstallation dan finish yang punya cek sync_token. Endpoint lain tidak dilindungi terhadap retry jaringan.
Dampak: Klien yang retry mendapat error 422 alih-alih response sukses yang sama.
**Status:** Diperbaiki penuh (2026-09-15) — `sync_token` kini diterima di startTrip/arrive/cancel/fail (ReasonRequest + helper transisi di WorkOrderController) dan diteruskan ke `WorkOrderTransitionService::transition()`. Token tercatat di `metadata->sync_token` pada history transisi; retry dengan token yang sama direspons sukses tanpa transisi ulang dan tanpa event. Berlapis dengan idempotency status (`fromStatus === toStatus`).

### 13. Tidak Ada Throttling Persistensi Tracking Point (30 detik) — ✅ [SELESAI]
File: app/Modules/Tracking/Jobs/PersistTrackingPoint.php:28-31
Bug: Setiap request GPS langsung dispatch job yang menulis baris baru ke tracking_points. Tidak ada cek interval 30 detik. Bisa menghasilkan hingga 120 baris/menit per teknisi.
Dampak: Pembengkakan database, melanggar NFR-PERF-006.
**Status:** Diperbaiki — job kini membaca `config('notifications.tracking.persist_interval_seconds', 30)` dan skip jika sudah ada TrackingPoint dengan `recorded_at` dalam interval tersebut (PersistTrackingPoint.php:28-38; key config ada di config/notifications.php:91).

### 14. is_mocked Tidak Disertakan di broadcastWith() — ✅ [SELESAI]
File: app/Modules/Tracking/Events/TrackingLocationUpdated.php:44-51
Bug: Field is_mocked divalidasi dan disimpan ke DB, tetapi tidak disertakan di payload broadcast. Customer/dashboard tidak tahu jika lokasi adalah Fake GPS.
**Status:** Diperbaiki — `'is_mocked' => $this->location['is_mocked'] ?? false` kini ada di payload broadcast (TrackingLocationUpdated.php:51).

### 15. Missing Audit Trail di Banyak Aksi (FR-AUTH-005) — ✅ [SELESAI]
File: Multiple (AuthController.php, AttendanceAdminController.php, DashboardController.php, dll.)
Bug: Aksi seperti changePassword, setPin, resetPin, toggleFakeGps, storeLocation, updateLocation, updateTechnician tidak mencatat siapa pelaku dan kapan.
Dampak: Tidak ada audit trail untuk tindakan administratif.
**Status:** Diperbaiki (2026-09-15) — fondasi audit trail dibuat: modul baru `app/Modules/Audit/` (`AuditLog` model + `AuditTrailService::record(action, subject, properties, actor)`), tabel `audit_logs` (user_id, action, polymorphic subject, properties jsonb, ip_address, created_at + indeks). Terpasang di: API changePassword & setPin, Web ProfileController (ganti password), DashboardController resetPin & toggleFakeGps, AttendanceAdminController storeLocation/updateLocation/updateTechnician/reviewLeave. Aksi tercatat siapa + kapan + IP.

### 16. Memory Leaks di App.vue — Tidak Ada beforeUnmount — ✅ [SELESAI]
File: mobile/src/App.vue
Bug: Tidak ada hook beforeUnmount/unmounted. Banyak event listener (window, PushNotifications, Capacitor App), timer (pollTimer, gpsTimer, attendanceClockTimer), dan watcher (geolocation, BackgroundGeolocation) tidak pernah dibersihkan.
Dampak: Memory leak jika komponen di-unmount.
**Status:** Diperbaiki penuh (2026-09-15) — `beforeUnmount()` kini memanggil `teardownListeners()`: listener `online`/`offline`/`visibilitychange`/`beforeinstallprompt`/`appinstalled` (termasuk yang di level modul) disimpan sebagai handler bernama lalu di-`removeEventListener`; 3 handle `PushNotifications.addListener` dikoleksi di `_pushHandles` dan handle `backButton` Capacitor disimpan — semuanya di-`remove()` saat unmount (fire-and-forget via Promise.resolve). Timer/GPS/map/progress-Filesystem sudah dibersihkan sejak perbaikan sebelumnya. Bonus: kurung kurawal berlebih di `saveOfflineSyncQueue` (pecah build) ikut diperbaiki; `npx vite build` kini sukses.

### 17. checkMockLocation() Tidak Di-await di loadAttendance — ✅ [SELESAI]
File: mobile/src/App.vue:1721-1733
Bug: checkMockLocation() dipanggil tanpa await, sehingga isMocked masih false saat UI render. User bisa menekan tombol absen sebelum deteksi Fake GPS selesai.
**Status:** Diperbaiki — `await this.checkMockLocation();` di loadAttendance (≈App.vue:1739-1750), dan juga di-await di flow clock-in (≈App.vue:3042).

### 18. syncItems Hanya Salin 1 Item dari Legacy — ✅ [SELESAI]
File: app/Modules/Legacy/Services/LegacyWorkOrderService.php:207-229
Bug: Hanya membuat 1 item (gabungan car_brand + car_model), bukan list lengkap dari SHOW_SalesDetail. Detail multiple windows, positions, dimensions tidak tersalin.
**Status:** Diperbaiki (2026-09-15) — `createFromSales()` kini membaca `salesDetails()` (SHOW_SalesDetail) SEBELUM transaksi FSM (tetap menghindari cross-DB transaction), dan `syncItems()` menulis item kendaraan (dipertahankan demi kompatibilitas) **plus satu item per baris detail**: `inventory_name`, posisi kaca + detail posisi (via `SalesDetailMapper`) + dimensi `width x length` dirangkai ke `window_film_desc`, `quantity` dari kolom qty (min 1). Bila detail kosong, perilaku lama (hanya item kendaraan) tetap.

### 19. realtime_channel Diekspos di Endpoint Publik Tanpa Auth — ✅ [SELESAI]
File: app/Http/Controllers/Api/V1/TrackingTokenController.php:151
Bug: Endpoint publik GET /api/v1/public/tracking/{token} mengembalikan realtime_channel. Karena channel bersifat public (Bug #4), siapa saja bisa subscribe tanpa autentikasi tambahan.
**Status:** Diperbaiki — payload endpoint publik tidak lagi menyertakan `realtime_channel` (TrackingTokenController.php:138-154); lookup token via hash sha256 + cek expiry/status. Kombinasi dengan fix #4 (PrivateChannel + authorization callback) menutup celah ini; halaman tracking web mengambil channel dari controller web yang ter-autentikasi (TrackingPageController), bukan endpoint publik.

---

## SEDANG

### 20. Transisi waiting_acceptance → rejected Tidak Ada di TRANSITIONS Map — ✅ [SELESAI]
File: app/Modules/WorkOrder/Services/WorkOrderTransitionService.php:34
Bug: TRANSITIONS map hanya mendaftarkan waiting_acceptance → cancelled, bukan → rejected. AssignmentService melakukan update langsung (bypass TRANSITIONS), menyebabkan validasi state machine tidak konsisten.
**Status:** Diperbaiki — `'waiting_acceptance' => [WorkOrderStatus::Rejected, WorkOrderStatus::Cancelled]` (WorkOrderTransitionService.php:34).

### 21. TTL Cache Lokasi Hardcoded (2 menit) — ✅ [SELESAI]
File: app/Http/Controllers/Api/V1/TrackingLocationController.php:36
Bug: now()->addMinutes(2) hardcoded. Spesifikasi menyatakan TTL harus konfigurasi, bukan hardcoded.
**Status:** Diperbaiki — TTL diambil dari `config('notifications.tracking.location_cache_ttl_seconds', 120)` (TrackingLocationController.php:36-37; key ada di config/notifications.php:90).

### 22. Migration after('accuracy') — Kolom Tidak Ada — ✅ [SELESAI]
File: database/migrations/2026_08_20_153951_add_is_mocked_to_tracking_points_table.php:15
Bug: ->after('accuracy') tetapi kolom sebenarnya accuracy_meters. No-op di PostgreSQL, tapi gagal di MySQL.
**Status:** Diperbaiki — kini `->after('accuracy_meters')`, kolom tersebut memang ada di tabel tracking_points (migration create: line 16).

### 23. Sanctum Token Tidak Ada Expiration — ✅ [SELESAI]
File: config/sanctum.php:53
Bug: 'expiration' => null — token tidak pernah kedaluwarsa. Token bocor tetap valid selamanya.
**Status:** Diperbaiki — `'expiration' => (int) env('SANCTUM_EXPIRATION_MINUTES', 43200)` (sanctum.php:53). Catatan: env var belum dipasang di .env/.env.example sehingga default efektif 30 hari — boleh dikencangkan sesuai kebutuhan.

### 24. setPin() Tanpa Verifikasi PIN/Password Lama — ✅ [SELESAI]
File: app/Http/Controllers/Api/V1/AuthController.php:68-75
Bug: PIN bisa direset tanpa verifikasi PIN lama atau password.
**Status:** Diperbaiki — SetPinRequest mewajibkan `current_pin` dan memverifikasinya via `Hash::check` bila user sudah punya pin_hash (SetPinRequest.php:22-36). Set PIN pertama kali (belum ada pin_hash) tetap tanpa verifikasi — sesuai desain onboarding.

### 25. verifyPin Brute Force — 6-digit PIN dengan 60/menit — ✅ [SELESAI]
File: routes/api.php:29
Bug: POST auth/pin/verify hanya throttle:api (60/menit). 1 juta kombinasi → brute force ~2.8 jam. Tidak ada lockout.
**Status:** Diperbaiki — endpoint kini pakai `throttle:login` (5/menit, routes/api.php:28). Catatan: lockout per-akun setelah N gagal masih belum ada; limiter membatasi per IP (field email tidak ada di request ini).

### 26. Race Condition — loadOrders(true) Tanpa Guard Overlap — ✅ [SELESAI]
File: mobile/src/App.vue:1420, 1861, 2503
Bug: Polling setiap 45 detik tanpa proteksi overlap. Jika jaringan lambat, dua fetch paralel bisa menimpa hasil.
**Status:** Diperbaiki — `loadOrders()` punya mutex `if (this._loadingOrders) return; ... finally { this._loadingOrders = false; }` (≈App.vue:1897-1911); tick polling yang overlap di-skip.

### 27. Offline Queue — Foto Base64 Bisa Melebihi Quota localStorage — ✅ [SELESAI]
File: mobile/src/App.vue:1583-1584
Bug: saveOfflineSyncQueue() tidak di-wrap try/catch. QuotaExceededError akan propagate. Beberapa foto bisa mencapai 5-10MB limit.
**Status:** Diperbaiki — ditulis dalam try/catch; saat QuotaExceededError队列 di-trim (`slice(-1)`) dan ditulis ulang dalam nested try/catch yang gagal diam-diam (≈App.vue:1590-1602).

### 28. pinLogin Tidak Cek Status Akun (Soft Delete) — ✅ [SELESAI]
File: app/Http/Controllers/Api/V1/AuthController.php:88-96
Bug: Tidak ada pengecekan deleted_at atau status akun. Akun yang sudah di-nonaktifkan tetap bisa login.
**Status:** Diperbaiki (implisit) — model User memakai SoftDeletes dan tidak ada satu pun `withTrashed()` di app/, sehingga query lookup pinLogin otomatis mengecualikan akun yang di-soft-delete. Catatan: belum ada kolom status akun eksplisit (nonaktif tanpa delete belum tercover).

### 29. Cross-Database Transaction — Event Dispatch Bisa Ghost — ✅ [SELESAI]
File: app/Modules/Legacy/Services/LegacyWorkOrderService.php:45-83
Bug: Query ke DB lama (koneksi berbeda) di dalam DB::transaction() FSM. Jika transaksi outer rollback, event yang sudah dispatch (notification queue) tidak undo → ghost notification.
**Status:** Diperbaiki penuh (2026-09-15) — dua-duanya tuntas: (a) semua query DB legacy (salesBySerial, salesDetails, importBySerials) dieksekusi SEBELUM `DB::transaction()` FSM dibuka; (b) semua dispatch event (`AssignmentCreated`, `AssignmentResponded`, `AssignmentSuperseded`, `WorkOrderStatusChanged`) di `AssignmentService` dan `WorkOrderTransitionService` dibungkus `DB::afterCommit(...)` → hanya jalan setelah transaksi TERLUAR commit; rollback outer tidak lagi menghasilkan ghost notification. Saat tidak ada transaksi aktif, `DB::afterCommit` berjalan langsung sehingga perilaku API biasa tidak berubah. `AssignmentSuperseded` yang tadinya di-dispatch dari DALAM closure transaksi kini dikoleksi dan di-dispatch setelah commit.

### 30. Race Condition di Duplicate WO Check — ✅ [SELESAI]
File: app/Modules/Legacy/Services/LegacyWorkOrderService.php:169-176
Bug: Dua query terpisah (exists() lalu first()) tanpa lockForUpdate(). Concurrent request bisa lewat cek duplikat.
**Status:** Diperbaiki — cek duplikat kini `->lockForUpdate()->exists()` (LegacyWorkOrderService.php:174-176). Catatan: guard sebenarnya tetap unique constraint pada `work_orders.number` (lock pada row yang belum ada tidak mencegah concurrent INSERT di Postgres) — constraint tersebut ada dan menjadi pelindung final.

### 31. LIMIT Tanpa ORDER BY di SQL Legacy — ✅ [SELESAI]
File: app/Modules/Legacy/Services/LegacyDataSourceService.php:138-160
Bug: LIMIT tanpa ORDER BY di SQL, sort di PHP. PostgreSQL return baris acak, hasil sort tidak akurat.
**Status:** Diperbaiki — query teknisi pakai `ORDER BY u.full_name` dan query sales pakai `ORDER BY s.serial DESC` sebelum LIMIT (LegacyDataSourceService.php:119, 138). Sisanya `LIMIT 1` dengan filter kolom unik (serial) sehingga deterministik.

### 32. Bundle Download Tanpa Auth & Tanpa Validasi Versi — ✅ [SELESAI]
File: app/Http/Controllers/Api/V1/AppVersionController.php:39-48
Bug: Endpoint download bundle tidak butuh auth. int $version bisa negatif/0. Siapa saja yang tahu URL bisa download.
**Status:** Diperbaiki — endpoint download kini divalidasi (`->where('version', '[0-9]+')` + `abort_if($version < 1, 404)`) dan dilindungi **signed URL** 24 jam (`URL::temporarySignedRoute`, middleware `signed`), bukan token. Catatan penting: solusi awal berupa `auth:sanctum` terbukti akan **memutus OTA** — yang mengunduh bundle adalah downloader natif (CapacitorUpdater) tanpa header Authorization, dan cek versi terjadi sebelum login; seluruh APK lama tidak akan pernah bisa update. Signed URL menutup "URL tebakan bisa diunduh" (403 tanpa signature) sambil tetap kompatibel dengan APK yang sudah terpasang. Terverifikasi: `/app/version` → 200 dengan `bundle_url` bertanda tangan; tanpa signature → 403.

### 33. getAddressFromCoords Dead Code — Regresi Potensial — ✅ [SELESAI]
File: app/Http/Controllers/Api/V1/AttendanceController.php:21-42
Bug: Metode sinkron tanpa timeout/cache masih ada tapi tidak dipanggil. Jika seseorang memanggilnya kembali, bug P1 lama akan kembali.
**Status:** Diperbaiki — method dihapus total (tidak ada referensi di app/ & routes/). Resolusi alamat kini via job `ResolveAttendanceAddress` dengan connectTimeout 3s / timeout 6s / retry dan cache 30 hari.

### 34. attendanceServerNow() — Vue Reactivity Tidak Bekerja — ✅ [SELESAI]
File: mobile/src/App.vue:1739-1742
Bug: Baris this.attendance.serverClockTick; adalah statement kosong. Vue tidak re-evaluate method di template hanya karena properti diakses tetapi tidak digunakan dalam return value. Jam absensi tidak update real-time.
**Status:** Diperbaiki — `serverClockTick: 0` kini field reaktif di data (≈App.vue:1194), di-increment tiap detik oleh timer (≈App.vue:1755), dan dibaca via `void this.attendance.serverClockTick;` di attendanceServerNow() (≈App.vue:1757-1762) sehingga render depend padanya — jam update real-time.

### 35. goHome() Tidak Memanggil stopGps() — GPS & Map Orphan — ✅ [SELESAI]
File: mobile/src/App.vue:1929-1932
Bug: goHome() tidak membersihkan GPS dan map instance. GPS tetap berjalan, Leaflet map orphan di memory.
**Status:** Diperbaiki — `goHome() { this.stopGps(); this.destroyMap(); this.view = 'home'; this.current = null; }` (≈App.vue:1952-1956).

---

## RENDAH

### 36. Dead Code getAddressFromCoords — ✅ [SELESAI]
**Status:** Sama dengan #33 — method sudah dihapus.

### 37. LeaveRequest Tidak Cast start_time/end_time — ✅ [SELESAI]
File: app/Modules/Attendance/Models/LeaveRequest.php:13-16
Inkonsisten dengan praktik cast model lain.
**Status:** Diperbaiki — `casts()` kini berisi `'start_time' => 'datetime:H:i'` dan `'end_time' => 'datetime:H:i'` (LeaveRequest.php:15).

### 38. Tumpang Tindih Cuti Belum Dicek (P3 dari audit) — ✅ [SELESAI]
File: app/Http/Controllers/Api/V1/AttendanceController.php:140-149
Admin bisa approve beberapa pengajuan untuk periode sama.
**Status:** Diperbaiki penuh (2026-09-15) — cek overlap saat PENGAJUAN sudah ada (`storeLeave()` menolak request yang tumpang tindih dengan pending/approved). Kini ditambah re-check saat APPROVE: `AttendanceAdminController::reviewLeave()` menolak persetujuan bila sudah ada pengajuan APPROVED lain pada karyawan yang sama dengan rentang tanggal yang sama (error balik ke form). Invarian "tidak ada dua approved yang tumpang tindih" terjaga, termasuk untuk data lama yang keburu overlap.

### 39. recordHistory Tidak Isi metadata — ✅ [SELESAI]
File: app/Modules/WorkOrder/Services/WorkOrderTransitionService.php:250-265
History dari transisi tidak punya metadata kontekstual.
**Status:** Diperbaiki — `recordHistory()` kini mengisi `'metadata' => ['source' => 'transition', 'assignment_id' => ..., 'actor_role' => ...]` (WorkOrderTransitionService.php:272-276).

### 40. useLegacyBridge: true Deprecated di Capacitor 6 — ✅ [SELESAI]
File: mobile/capacitor.config.json:7
Flag deprecated sejak Capacitor 5, dihapus di Capacitor 6.
**Status:** Diperbaiki — flag dihapus dari sumber `mobile/capacitor.config.json`. Aset Android (`mobile/android/app/src/main/assets/capacitor.config.json`) sudah di-regenerate via `npx cap sync` (2026-09-15) — kini bersih, sumber & aset seragam.

### 41. N+1 Query di LegacyTechnicianImporter — ✅ [SELESAI]
File: app/Modules/Legacy/Services/LegacyTechnicianImporter.php:28-37
1 query per serial ke DB lama + 1-2 query ke DB FSM. ~60-80 query untuk 20 teknisi.
**Status:** Diperbaiki — sisi DB legacy kini batch: `techniciansBySerials()` dengan satu `WHERE serial IN (...)` (LegacyDataSourceService.php:190-203, dipanggil importer line 35). Catatan: loop upsert sisi FSM per-teknisi masih ada, tetapi jauh lebih murah (DB lokal, sudah ter-batch query utamanya).

### 42. CapacitorUpdater.autoUpdate: false — ✅ [SELESAI]
File: mobile/capacitor.config.json:10-12
notifyAppReady() dipanggil tetapi tanpa auto-update, Capgo tidak akan auto-check bundle.
**Status:** Diperbaiki — `plugins.CapacitorUpdater.autoUpdate: true` kini diatur di `mobile/capacitor.config.json`, konsisten dengan `CapacitorUpdater.notifyAppReady()` di mounted. Aset Android sudah diselaraskan via `npx cap sync` (2026-09-15); build APK berikutnya otomatis memakai config baru.

---

## Riwayat Perbaikan Sisa (2026-09-15)

| # | Bug | Penyelesaian |
|---|---|---|
| 15 | Audit trail (KRITIS) | Modul `app/Modules/Audit/` (tabel audit_logs + AuditTrailService) dipasang di 9 aksi sensitif |
| 18 | syncItems lengkap (TINGGI) | Menyalin seluruh baris SHOW_SalesDetail + item kendaraan |
| 12 | Idempotency endpoint transisi | sync_token via history `metadata->sync_token` di startTrip/arrive/cancel/fail |
| 16 | Memory leaks App.vue | Semua listener window/document/PushNotifications/backButton dilepas di beforeUnmount |
| 29 | Ghost event dispatch | Semua dispatch event dibungkus `DB::afterCommit` |
| 38 | Overlap cuti saat approve | Re-check overlap di `reviewLeave()` sebelum approve |
| 40/42 | Config Android basi | `npx cap sync` dijalankan — aset Android kini seragam dengan sumber |

Perbaikan bonus ditemukan di rute verifikasi: (a) kurung kurawal berlebih `saveOfflineSyncQueue()` (App.vue) yang membuat build mobile pecah; (b) `StoreLegacyWorkOrderRequest::authorize()` kini menolak teknisi dengan 403 sebelum validasi 422 (menyembunyikan skema payload dari user tak berwenang).

**Hasil verifikasi akhir:** `php artisan test` → 45 passed; `npx vite build` (mobile) → sukses; migrasi `audit_logs` jalan; `/dashboard` → 200 (coordinator) / 403 (teknisi).

---
---

# AUDIT RONDE 2 — 2026-09-15 (malam) — 30 Temuan Baru

> Audit ulang seluruh project (3 auditor: HTTP layer/security, services/domain, mobile/frontend). Semua temuan sudah diverifikasi terhadap kode (✓ = dicek manual, ● = laporan auditor dengan keyakinan tinggi).
> **Status: SEMUA 30 item diperbaiki 2026-09-16** (✓ = diverifikasi manual saat perbaikan).
> Catatan deploy: produksi saat ini = commit `03accf9`. OTA `20.zip` masih bermasalah di sisi file server (bukan kode) — lihat `storage/app/private/bundles/` + permission `www`.

## 🔴 A. KRITIS/TINGGI — dampak langsung terasa

### A1. Ganti PIN di aplikasi teknisi selalu gagal 422 — ✅ [SELESAI 2026-09-16]
File: mobile/src/App.vue:2407 vs app/Http/Requests/Api/V1/SetPinRequest.php:22-36
Bug: `submitChangePin` POST `/auth/pin` hanya kirim `{ pin: newPin }`. Sejak fix #24, `SetPinRequest` mewajibkan `current_pin` (+`Hash::check`) bila user sudah punya `pin_hash` — dan kondisi "ganti PIN" selalu begitu. Response 422 → UI loop "Gagal menyimpan PIN baru."
**Regresi dari fix #24.**
Saran perbaikan: kirim `current_pin: oldPin` di body (variabel `oldPin` sudah ada di flow, sudah dicek `serverPinCheck` di :2397). Set tip pada setup-PIN pertama kali tetap aman (tidak ada pin_hash → tidak wajib).
Confidence: ✓ tinggi.

### A2. 🤖 Halaman tracking pelanggan kehilangan realtime — ✅ [SELESAI 2026-09-16]
File: resources/views/tracking/show.blade.php:765-781, app/Http/Controllers/Api/V1/TrackingTokenController.php (payload 138-182), app/Modules/Tracking/Events/TrackingLocationUpdated.php:26-35
Bug (3 lapis, hasil kombinasi fix #4 + #19):
1. Blade membaca `data.realtime_channel` dari GET `/public/tracking/{token}` — field dihapus fix #19 → guard `!data.realtime_channel` selalu true → Echo tidak pernah connect.
2. Blade subscribe `.channel('tracking.'+ch)` (public) padahal server kini broadcast ke `PrivateChannel("tracking.{ch}")`.
3. Guest pelanggan tidak bisa authorize `/broadcasting/auth` (butuh sesi web) → PrivateChannel mustahil untuk audiens utamanya.
Dampak: badge "LIVE"/"Posisi diperbarui otomatis" bohong; semua pelanggan fallback polling 8 detik. Klaim Bugs.md #19 bahwa halaman mengambil channel dari controller web TIDAK berlaku di kode (TrackingPageController hanya kirim `$token`).
Saran perbaikan (butuh keputusan): **restore realtime berbasis kepemilikan token** — payload publik dikirimi `realtime_channel` LAGI (hanya untuk token valid/aktif/non-expired — token = kredensial guest), dan broadcast `tracking.{ch}` kembali ke public `Channel` dengan penamaan 32-char acak; `PrivateChannel("work-order.{id}")` (dashboard, user terautentikasi) tetap. Perbaiki juga catatan #4/#19 di Ronde 1 agar jujur. Alternatif aman: accept polling-only + hapus badge LIVE.
Confidence: ✓ tinggi.

### A3. Guard "push tanpa device token" mati total (enum vs string) — ✅ [SELESAI 2026-09-16]
File: app/Modules/Notification/Services/NotificationDeliveryService.php:29, app/Modules/Notification/Jobs/DeliverNotification.php:54
Bug: `$notification->channel === NotificationChannel::Push->value` — kolom `channel` di-cast ke enum (Notification.php:35), sehingga `enum === string` SELALU false. Guard tidak pernah jalan → push tanpa device token = 3× retry (backoff 10/60/300) + 3 exception log per notifikasi (retry storm yang ingin dicegah).
Saran perbaikan: bandingkan `=== NotificationChannel::Push` (enum vs enum) di kedua file.
Confidence: ✓ tinggi.

### A4. Edit WO: teknisi yang dihapus lalu dicentang ulang tidak pernah di-assign lagi — ✅ [SELESAI 2026-09-16]
File: app/Http/Controllers/Web/DashboardController.php:466-470 + loop hapus :493-519
Bug: `$currentSerials` di-pluck dari SEMUA assignment termasuk `Cancelled`/`Superseded` → teknisi lama tidak muncul di `$newSerials` (diff) saat dicentang ulang, dan loop hapus skip karena `desired->contains(...)` true. Assignment hilang senyap; flash "berhasil" tetap tampil. Tambahan: tidak ada guard status WO di `updateWorkOrder` (:406-452) — assignment Pending bisa dibuat di WO `accepted`/`finished`, padahal `respond()` mensyaratkan `WaitingAcceptance` (AssignmentService.php:125-127) → tidak bisa pernah di-accept.
Saran perbaikan: (1) `currentSerials` hanya dari assignment status `Pending`/`Accepted`; (2) guard: sinkronisasi teknisi hanya untuk status `draft`/`waiting_acceptance`/`rejected` (deny dengan pesan jelas); (3) bungkus seluruh `updateWorkOrder` dalam `DB::transaction` (lihat B21) dan dispatch `AssignmentCreated`-nya via `DB::afterCommit` (konsisten #29).
Confidence: ✓ tinggi.

### A5. Otorisasi channel tracking bisa dilewati semua user — ✅ [SELESAI 2026-09-16]
File: routes/channels.php:20-30
Bug: `whereHas('workOrder', fn($q) => $q->whereHas('assignments', ...)->orWhereIn('status', [OnTheWay, Arrived, Installation]))` — `orWhereIn` menempel di level workOrder → SATU cabang OR saja cukup: user terautentikasi mana pun lolos authorisasi channel sesi aktif teknisi lain. Mitigasi cuma nama channel acak (security-by-secrecy).
Saran perbaikan: rapikan group: `->where(function($q){ $q->whereHas('assignments',...)->orWhere('customer_id', ...) })` atau cukup syarat assignments + (jika A2 diputuskan tetap private). Relevan juga untuk dashboard viewer.
Confidence: ✓ tinggi.

### A6. `realtime_channel` bocor mentah via WorkOrderResource — ✅ [SELESAI 2026-09-16]
File: app/Http/Resources/Api/V1/WorkOrderResource.php:59
Bug: `'tracking_sessions' => $this->whenLoaded('trackingSessions')` me-serialisasi MODEL penuh → siapa pun yang lolos `WorkOrderPolicy::view` (termasuk sesama teknisi) membaca `realtime_channel` sesi teknisi lain.
Saran perbaikan: ubah ke collection field whitelist: `id, status, started_at, ended_at` (cek dulu konsumen: mobile tidak memakai realtime_channel; dashboard blade — cek sebelum trim).
Confidence: ✓ tinggi.

### A7. Impor teknisi legacy vs user soft-deleted → 500 / listener crash — ✅ [SELESAI 2026-09-16]
File: app/Modules/Legacy/Services/LegacyTechnicianImporter.php:54-74
Bug: `User::firstOrCreate(['email'=>...])` tak melihat baris soft-deleted (global scope) tapi unique index email tetap menabrak → `QueryException 23505` → createFromSales 500 saat re-import. Jalur update: `$technician->user()->update()` no-op senyap bila user terhapus sementara `is_active=true` → teknisi yatim → `RecordAssignmentCreatedNotification.php:18` (`$technician->user->email` tanpa null-check) fatal di queue (retry beruntun). Pola sama di `RecordAssignmentRespondedNotification.php:20-25`.
Saran perbaikan: (1) importer: `User::withTrashed()->firstOrNew(['email'])` → `restore()` bila trashed, lalu update field; (2) guard null di kedua listener (skip + Log::warning); (3) dokumentasikan efek samping: importBySerials jalan sebelum transaksi FSM → rollback meninggalkan user/technician orphan (receh, bukan blocker).
Confidence: ● sedang-tinggi.

### A8. Unlock via PIN mati setelah token sesi kedaluwarsa — ✅ [SELESAI 2026-09-16]
File: mobile/src/App.vue:2428-2531 (serverPinCheck + verifyPin)
Bug: `serverPinCheck` mengirim `Authorization: Bearer <token lama>`; token expired → 401 → dipetakan `'error'` ("periksa koneksi") → cabang `pinLogin()` di verifyPin (:2526) tak pernah tercapai. Recovery-after-expiry yang didesain #7 jadi tidak bisa; teknisi dipaksa password. `tryBiometric` (2600-2615) juga unlock dengan token mati → 401 loop.
Saran perbaikan: `serverPinCheck` bedakan 401 → return `'expired'`; `verifyPin`: status `'expired'` (dan `'wrong'`) lanjut `pinLogin(email, pin)` — 422 → "PIN salah", sukses → simpan token baru lalu unlock. (Catatan: `serverPinCheck` pakai `this.token` — untuk kasus expired memang tak bisa; pinLogin tidak butuh token.)
Confidence: ● tinggi.

## 🟠 B. SEDANG

### B9. Tabrakan sync_token lintas-stage + foto completion tak pernah tampil di API — ✅ [SELESAI 2026-09-16]
File: app/Http/Controllers/Api/V1/WorkOrderController.php:136-139 (startInstallation) & :176-179 (finish), app/Http/Resources/Api/V1/WorkOrderResource.php:43-58
Bug: cek idempotensi `photos()->where('sync_token', ...)` tidak memfilter `stage` → klien yang memakai SATU token untuk start lalu finish membuat finish "sukses" palsu tanpa transisi. Selain itu `finish()` tidak menulis `stage` (default migrasi = `completion`), sedangkan resource hanya me-grup `before_installation` & `after_installation` → foto penyelesaian tidak pernah muncul via API.
Saran perbaikan: filter cek per-stage (`where('stage','before_installation')` / `'completion'`), set `stage` eksplisit di finish, dan tambahkan group `completion` di resource (cek kunci yang dibaca App.vue sebelum rename).
Confidence: ✓ tinggi.

### B10. Re-check overlap #38 punya 3 celah (perbaikan sendiri) — ✅ [SELESAI 2026-09-16]
File: app/Http/Controllers/Web/AttendanceAdminController.php:101-112
Bug: (1) dua `permission` jam berbeda di hari sama kini ikut diblokir saat approve (cek hanya rentang tanggal — kasus sah); (2) baris lama dengan `leave_end_date NULL` lolos (`where('leave_end_date','>=',...)` tak pernah true untuk NULL); (3) tak ada guard `status = pending` → review ulang approved↔rejected bebas, dan jalur toggle melewati cek overlap.
Saran perbaikan: (1) bila kedua request tipe permission dengan jam → syarat overlap juga irisan jam (`start_time < other.end_time AND end_time > other.start_time`); (2) pakai `COALESCE(leave_end_date, leave_date)`; (3) `abort_unless($leaveRequest->status === 'pending')` di awal (atau flow re-review eksplisit).
Confidence: ✓ tinggi.

### B11. Replay sync_token melewati otorisasi — ✅ [SELESAI 2026-09-16]
File: app/Modules/WorkOrder/Services/WorkOrderTransitionService.php:57-62
Bug: cabang "token sudah pernah tercatat" return sukses tanpa `authorizeTransition`/`validateReason` → siapa pun yang tahu token orang lain bisa replay lintas peran.
Saran perbaikan: panggil `authorizeTransition` dengan try-catch `InvalidWorkOrderTransition` (status sudah maju) — `AuthorizationException` tetap propagate. (validateReason tak perlu: ReasonRequest `required`.)
Confidence: ✓ tinggi.

### B12. sync_token startTrip/arrive tak tervalidasi — ✅ [SELESAI 2026-09-16]
File: app/Http/Controllers/Api/V1/WorkOrderController.php (helper transition, ~:229-233)
Bug: `startTrip`/`arrive` pakai `Request` polos; nilai non-string (number/array) jadi `null` senyap → idempotency mati tanpa error; truncation `Str::substr(...,0,64)` → dua token berbeda berprefix sama bisa tabrakan dedup. ReasonRequest sudah benar (`max:64`).
Saran perbaikan: FormRequest kecil `TransitionRequest` (`sync_token nullable|string|max:64`) untuk startTrip/arrive; helper transition tinggal pakai `validated()`; hapus truncation.
Confidence: ✓ tinggi.

### B13. "Ganti Akun" tidak logout; pollTimer spam saat 401 — ✅ [SELESAI 2026-09-16]
File: mobile/src/App.vue:2212-2221 (softLogout), :1505-1517 (handler 401 api())
Bug: softLogout tidak hapus `fsm_tech_token`/`fsm_tech_user` & tidak panggil `DELETE /auth/logout` → cold start berikutnya auto-resume akun lama (privasi perangkat bersama). Handler 401 tidak `clearInterval(pollTimer)` → toast "Sesi berakhir" tiap 45 detik selamanya di lock screen.
Saran perbaikan: softLogout → `localStorage.removeItem(fsm_tech_token/fsm_tech_user)`, `this.token=null; this.user=null`, panggil `DELETE /auth/logout` (api, catch diam); di jalur 401 `api()`: `clearInterval(this.pollTimer); this.pollTimer = null;` sebelum set view lock.
Confidence: ● tinggi.

### B14. Paginasi /work-orders diabaikan client — ✅ [SELESAI 2026-09-16]
File: app/Http/Controllers/Api/V1/WorkOrderController.php:35 (paginate default 15, sort `scheduled_start_at` DESC) vs mobile/src/App.vue:1904-1905
Bug: client tidak kirim `?per_page` dan tak menelusuri `links.next` → WO penjadwalan lama hilang senyap dari Beranda/Riwayat; hitungan tab salah.
Saran perbaikan: client `GET /work-orders?per_page=200`; server clamp `$request->integer('per_page', 15)` max 200. (Opsi lanjutan: filter periode riwayat.)
Confidence: ✓ tinggi.

### B15. 🤖 Tombol "Laporkan Kendala" di status yang server tolak (409 permanen) — ✅ [SELESAI 2026-09-16]
File: mobile/src/App.vue:1338-1360 vs WorkOrderTransitionService TRANSITIONS :26-27
Bug: aksi `fail` ditampilkan di `accepted`/`on_the_way`; server hanya izinkan Failed dari `arrived`/`installation`. Teknisi yang bermasalah di jalan tak bisa melapor.
KEPUTUSAN PRODUK (default usulan saya): tambahkan transisi `accepted → failed` dan `on_the_way → failed` (+ cabang authorizeTransition untuk `accepted`), karena use-case "kendala di perjalanan" valid. Alternatif: sembunyikan tombol di luar arrived/installation.
Confidence: ✓ tinggi (mismatch-nya), keputusan: user.

### B16. Throttle tracking memakai jam klien + tanpa proteksi race — ✅ [SELESAI 2026-09-16]
File: app/Modules/Tracking/Jobs/PersistTrackingPoint.php:31-43, migration tracking_points (tanpa unique)
Bug: filter `recorded_at >= now()-interval` memakai timestamp yang dikontrol perangkat → jam maju = persistensi terblokir berjam-jam (trip hilang); jam mundur = throttle mati (bengkak baris lagi). Dua worker paralel bisa sama-sama lolos check-then-insert.
Saran perbaikan: throttle berdasar kolom SERVER `created_at` (`where('created_at','>=',now()->subSeconds($interval))`), plus `Cache::add("persist:session:$id", true, $interval)` sebagai lock atomik. `recorded_at` tetap untuk data tampilan.
Confidence: ● tinggi.

### B17. ensurePlaintextLink mengabaikan expires_at (+ race) — ✅ [SELESAI 2026-09-16]
File: app/Modules/Tracking/Services/TrackingTokenService.php:39-48
Bug: reuse token `status Active + token_plain_encrypted NOT NULL` tanpa cek `expires_at > now()` → dashboard menampilkan link yang sudah mati (endpoint publik akan 404). Dua request dashboard paralel bisa membuat dua token.
Saran perbaikan: tambah `->where('expires_at','>',now())`; (opsional) `Cache::lock('plaintext-link:'.$sessionId, 5)` untuk race.
Confidence: ✓ tinggi.

### B18. Token Revoked masih menyajikan data WO — ✅ [SELESAI 2026-09-16]
File: app/Http/Controllers/Api/V1/TrackingTokenController.php:52-63
Bug: short-circuit "terminal status" dieksekusi SEBELUM penolakan `status !== Active`; token yang di-revoke (re-issue/finish) tanpa mengubah `expires_at` tetap menyajikan nomor WO + alamat customer hingga kadaluarsa natural.
Saran perbaikan: pindah cek `status === Active` ke sebelum blok terminal (expired-check sudah di atas, aman).
Confidence: ✓ tinggi.

### B19. techniciansBySerials tanpa filter profil teknisi — ✅ [SELESAI 2026-09-16]
File: app/Modules/Legacy/Services/LegacyDataSourceService.php:198-202 vs query browse :24-26
Bug: import via API menerima serial user legacy mana pun (query `WHERE serial IN (...)` tanpa `status/user_type/division` yang dipakai browse) → non-teknisi bisa jadi teknisi FSM `is_active=true`.
Saran perbaikan: samakan WHERE clause dengan filter browse profil teknisi.
Confidence: ● sedang.

### B20. Offline queue: trim quota = buang semua job — ✅ [SELESAI 2026-09-16]
File: mobile/src/App.vue:1592-1605
Bug: saat QuotaExceeded, `slice(-1)` menyimpan hanya 1 job terakhir (job foto lain hilang permanen, file di Filesystem jadi yatim); bila 1 job saja melebihi quota → nested catch diam → banner `pendingSyncCount` bohong (di UI ada, di disk tidak).
Saran perbaikan: loop drop-oldest satu per satu sampai muat (`while (queue.length > 1) { queue.shift(); try save }`); kalau masih gagal juga: kosongkan queue + `showToast('Antrean sinkronisasi penuh, foto lama dibuang')` + update `reportOfflineSyncStatus()`.
Confidence: ● tinggi.

### B21. updateWorkOrder non-atomic — ✅ [SELESAI 2026-09-16]
File: app/Http/Controllers/Web/DashboardController.php:417-452 + syncTechnicians :501-507
Bug: WO/loksi/customer disimpan duluan; `syncTechnicians` bisa `ValidationException` setelahnya (teknisi accepted dihapus) → error tampil padahal edit lain sudah tersimpan sebagian.
Saran perbaikan: selidiki seluruh badan `updateWorkOrder` dalam `DB::transaction` (bareng A4).
Confidence: ● tinggi.

### B22. hasScheduleConflict tidak pernah aktif untuk WO legacy — ✅ [SELESAI 2026-09-16]
File: app/Modules/Assignment/Services/AssignmentService.php:200-208 (return false bila `scheduled_end_at` null) — semua WO legacy menulis null (LegacyWorkOrderService.php:~203)
Saran perbaikan: jendela default — `$end = $wo->scheduled_end_at ?? $wo->scheduled_start_at?->copy()->addHours((float) config('fsm.schedule_default_duration_hours', 3))` (tambah key di config/fsm.php). Atau isikan scheduled_end_at saat create dari legacy.
Confidence: ✓ tinggi (mekanisme), sedang (apakah by-design).

## 🟡 C. RENDAH

### C1. calendar() 500 saat ?month ngawur — ✅ [SELESAI 2026-09-16]
app/Http/Controllers/Api/V1/AttendanceController.php:103 — `Carbon::createFromFormat('Y-m', query)` tanpa validasi → InvalidFormatException. Perbaiki: validate `['month' => 'nullable|date_format:Y-m']`.
### C2. doLogin res.json() tanpa cek ok — ✅ [SELESAI 2026-09-16]
App.vue:1873 — respons HTML 500 → SyntaxError mentah ke UI. Pola sama helper api().
### C3. JSON.parse fsm_tech_user tanpa try/catch — ✅ [SELESAI 2026-09-16]
App.vue:1074 — value korup = app gagal mount permanen.
### C4. Hook audit tak failure-isolated — ✅ [SELESAI 2026-09-16]
AuditTrailService::record — bungkus internal try/catch + Log::error (audit insert gagal tak boleh membatalkan aksi yang sudah sukses, mis. PIN terlanjur diganti).
### C5. Aset root-relative di /mobile (PWA subpath) — ✅ [SELESAI 2026-09-16]
App.vue :22,72,100,146,173,230,323,625,713 `src="/assets/images/iml-logo.png"` → 404 saat diakses via /mobile (base './'). Ganti ke `assets/images/...` (relatif) — aman di APK & /mobile.
### C6. pendingFcmToken hanya di-flush saat doLogin — ✅ [SELESAI 2026-09-16]
App.vue:1879/2136-2149 — registrasi token gagal saat offline tak pernah dicoba ulang. Tambah `this.sendFcmToken()` di `_onlineHandler` dan setelah `unlock()`. `platform` hardcoded 'android' (2144).
### C7. Penerima notif status = assignment terakhir tanpa filter — ✅ [SELESAI 2026-09-16]
RecordWorkOrderStatusNotification.php:26-27 — `sortByDesc('assigned_at')->first()` bisa kena Superseded/Cancelled. Prioritaskan status Accepted.
### C8. json_encode baris legacy non-UTF8 → source_payload null senyap — ✅ [SELESAI 2026-09-16]
LegacyWorkOrderService.php:157 — pakai `JSON_INVALID_UTF8_SUBSTITUTE` + fallback log.
### C9. sales() Top-N salah potong — ✅ [SELESAI 2026-09-16]
LegacyDataSourceService.php:~138-160 — `ORDER BY s.serial DESC LIMIT n` lalu PHP sort by installation_date → kandidat salah. Samakan ORDER BY SQL dengan sort PHP.
### C10. Dead code App.vue — ✅ [SELESAI 2026-09-16]
:1428-1430 `if (false && serviceWorker)`, `isAndroidBrowser` tak pernah dibaca (1132,2235), `passModal.show` tak terpakai. Bersihkan.

## ⚙️ D. HARDENING ENVIRONMENT — ✅ [SELESAI semua 2026-09-16]

### D1. Trusted proxies kosong
bootstrap/app.php:19-21 — `->withMiddleware(fn ($m) => $m->trustProxies(at: ['127.0.0.1','::1']))` supaya IP klien benar untuk throttle/audit IP, TANPA `at:'*'` (anti spoofing). Saat ini stack nginx→fpm kebetulan OK (verified: signed URL produksi lolos, throttle per IP nyata), tapi wajib sebelum lewat CDN.
### D2. Limiter khusus pin-verify
routes/api.php:28 — `throttle:login` men-key `email.ip`; request verify tidak berisi email → key = IP saja. Tambah `RateLimiter::for('pin-verify', ... per user+ip, 5/min)` + pakai di route.
### D3. .env.example
Tambah `SANCTUM_EXPIRATION_MINUTES=` (dengan komentar), `APP_URL` wajib host publik (signed OTA), `APP_DEBUG=false`, `MOBILE_BUNDLE_URL` opsional.
### D4. Catatan produksi — 📌 OPSI (bukan bug kode)
OTA 20.zip gagal bukan karena kode: file belum ada/ditolak di `storage/app/private/bundles/` (permission `www`). Cek `tinker var_dump(Storage::disk("local")->exists("bundles/20.zip"))`. Ini tugas deploy, tidak ada perubahan kode — masih menunggu tindakan manual di VPS.

## ✅ Area ronde 2 yang terverifikasi BERSIH (tidak ada temuan)
Sweep IDOR web routes (semua coordinator-gated), pemetaan middleware↔limiter, policies terpasang semua, SQL legacy ter-parameter-bind, semantik `DB::afterCommit` (eksekusi langsung saat non-transaksi; nested aman), query `metadata->sync_token` compile benar di PostgresGrammar, cast `datetime:H:i` tidak merusak kolom time, fresh migration berurut aman, controller device-token/sync-status ter-scope user, audit module dasar (console-safe, fillable cocok), kontrak field API mobile lainnya cocok (kecuali A1/B9/B14), teardownListeners tidak double-registrasi (setup* dipanggil sekali; setupInstallPrompt guarded), tidak ada `console.log`/`realtime_channel` sisa di mobile/src.

## Status eksekusi (diperbaiki 2026-09-16)

Semua item A/B/C/D dikerjakan. Ringkasan keputusan & file yang disentuh:

- **A1** mobile kirim `current_pin` di `submitChangePin`. **A8** `serverPinCheck` return `'expired'` untuk 401; `verifyPin` pinLogin ulang saat expired; `submitChangePin` & `tryBiometric` ikut guard.
- **A2** (keputusan produk, disetujui): `TrackingLocationUpdated` kembali broadcast `Channel("tracking.{ch}")` public; `realtime_channel` dikembalikan ke payload publik **hanya** saat sesi aktif; halaman tracking pelanggan realtime hidup lagi.
- **A3** `channel === NotificationChannel::Push` (enum vs enum) di delivery + job. **A4+B21** `syncTechnicians` hanya hitung assignment Pending/Accepted + guard status + `DB::transaction` + `AssignmentCreated` via `DB::afterCommit`. **A5** callback `tracking.*` dibetulkan (coordinator/admin bebas, teknisi hanya miliknya). **A6** `WorkOrderResource` whitelist field `tracking_sessions`. **A7** importer pulihkan user soft-deleted (`resolveUser` withTrashed+restore) + null-guard dua listener.
- **B9** cek `sync_token` per-stage + `stage='completion'` eksplisit + grup `completion` di resource. **B10** `reviewLeave`: guard `status=pending`, `COALESCE(leave_end_date, leave_date)`, irisan jam untuk izin. **B11** replay `sync_token` lewati `authorizeTransition` (catch `InvalidWorkOrderTransition`). **B12** `TransitionRequest` baru untuk startTrip/arrive (buang coercion). **B15** transisi `accepted`/`on_the_way → failed` + cabang authorize. **B16** throttle persist pakai `created_at` (server) + `Cache::add` lock. **B17** `ensurePlaintextLink` filter `expires_at`. **B18** cek `status!==Active` sebelum short-circuit terminal. **B19** `techniciansBySerials` samakan filter profil. **B22** `hasScheduleConflict` pakai `config('fsm.schedule_default_duration_hours')`.
- **B13/B14/B20/C2/C3/C5/C6/C10** (App.vue): softLogout hapus sesi penuh + `DELETE /auth/logout`; `/work-orders?per_page=200` + clamp server; trim offline queue per-item; doLogin `res.json().catch`; `fsm_tech_user` parse aman; gambar jadi `./assets/…` + MobileController sematkan `<base href="/mobile/">` (SW cache → v12); flush FCM saat online/unlock; hapus dead code.
- **C1/C4/C7/C8/C9/D1/D2/D3**: validasi `month`; audit `try/catch` + return nullable; prioritas penerima notif Accepted; `json_encode` `JSON_INVALID_UTF8_SUBSTITUTE`; ORDER BY sales sesuai sort PHP; `trustProxies(['127.0.0.1','::1'])`; limiter `pin-verify` (user+IP); `.env.example` (APP_URL/SANCTUM/APP_DEBUG notes).
- **D4** tetap menunggu tindakan manual di VPS (bukan kode).

**Verifikasi:** `php artisan test` → **45 passed**; `npx vite build` → sukses; `php -l` bersih. **Belum di-OTA/di-deploy** — jalankan `.\release-ota.ps1` lalu `./deploy.sh` di VPS untuk menerapkan (mobile + backend), dan selesaikan D4 (letakkan/`chown` `20.zip` di `storage/app/private/bundles/`).
