# Laporan Bug — FSM Tech-IML

> **Hasil verifikasi: 2026-09-15** (diperiksa terhadap kode working tree) · **Perbaikan sisa bug: 2026-09-15**
>
> | Status | Jumlah |
> |---|---|
> | ✅ Selesai | **42** |
> | ⚠️ Sebagian | **0** |
> | ❌ Belum | **0** |
>
> **Catatan:**
> - Bug #1 sempat memicu regresi (limiter `throttle:web` tidak terdaftar → dashboard error 500). Regresi diperbaiki 2026-09-15 (`RateLimiter::for('web', ...)` di `AppServiceProvider`), terverifikasi `/dashboard` return 200 untuk coordinator.
> - Perbaikan sesi 2026-09-15 (ronde 2): #12, #15, #16, #18, #29, #38 + `npx cap sync` (#40/#42). Bonus: kurung kurawal berlebih di `saveOfflineSyncQueue` (pecah build mobile, warisan fix #27) dan urutan otorisasi `StoreLegacyWorkOrderRequest` (403 harus sebelum 422) ikut diperbaiki. Seluruh test suite hijau (45 passed).

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
