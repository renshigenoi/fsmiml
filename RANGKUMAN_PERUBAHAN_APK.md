# Rangkuman Perubahan — FSM Teknisi jadi APK Android

Dokumen ini merangkum seluruh pekerjaan mengubah app teknisi FSM (Indo Motor Lestari) dari PWA berbasis Blade menjadi **APK Android (Capacitor)** dengan fitur live-update, background GPS, biometric, dan push notification.

Tanggal: 5 Agustus 2026
Pendekatan: project Vue 3 + Vite + Capacitor **terpisah** di subfolder `mobile/` (migrasi bersih dari `resources/views/mobile/app.blade.php`).

---

## A. Ringkasan Fitur yang Ditambahkan

| Fitur | Plugin | Manfaat |
|---|---|---|
| APK Android | Capacitor 6 | App native, bisa offline (bundle lokal di APK) |
| Live update (OTA) | `@capgo/capacitor-updater` | Ubah tampilan/UI **tanpa install ulang APK** |
| Background GPS | `@capacitor-community/background-geolocation` | Tracking tetap jalan saat app di-minimize |
| Biometric | `@aparajita/capacitor-biometric-auth` | Unlock via sidik jari / Face |
| Push notification | `@capacitor/push-notifications` | Notifikasi WO baru/update via FCM |

---

## B. File BARU — Frontend (folder `mobile/`)

Ini project Vue 3 + Vite + Capacitor yang baru dibuat dari nol, migrasi dari `app.blade.php`.

| File | Keterangan |
|---|---|
| `mobile/package.json` | Definisi dependency & script (dev, build, sync, android). Berisi 8 dependency termasuk 5 plugin Capacitor. |
| `mobile/capacitor.config.json` | Config Capacitor: `appId=com.iml.fsm.technician`, `webDir=dist`, plugin Capgo. **JSON murni** (bukan .ts/.js) supaya bebas error TypeScript/ESM. |
| `mobile/vite.config.js` | Config Vite: `base: './'` (WAJIB agar aset relatif jalan di APK), port dev 5174. |
| `mobile/index.html` | Entry point Vite (Google Fonts + mount `#app`). |
| `mobile/src/main.js` | Bootstrap Vue (`createApp(App).mount('#app')`). |
| `mobile/src/App.vue` | **File utama (~1900 baris)** — seluruh UI + logika teknisi hasil migrasi dari `app.blade.php`. Berisi semua fitur baru. |
| `mobile/src/composables/api.js` | Base URL API terpusat (`API_BASE`/`API_V1` dari `VITE_API_BASE`). **Kunci** agar API call jalan di dalam APK. |
| `mobile/src/assets/css/app.css` | Import Leaflet CSS + reset html/body/#app. |
| `mobile/.env.example` | Contoh `VITE_API_BASE` (emulator/HP fisik/production). |
| `mobile/.env` | Config aktif (berisi `VITE_API_BASE=https://fsm.indomotorlestari.com`). |
| `mobile/.gitignore` | Abaikan node_modules, dist, .env, android/, keystore. |
| `mobile/README.md` | Panduan build lengkap: prasyarat, config API, plugin, permission, versioning, OTA. |

### Detail perubahan penting di `mobile/src/App.vue`

| Bagian | Perubahan |
|---|---|
| Import plugin | Leaflet, BiometricAuth, CapacitorUpdater, PushNotifications, BackgroundGeolocation (via registerPlugin). |
| API call | Semua `fetch('/api/v1...')` → `fetch(API_V1 + ...)` (absolut, wajib untuk APK). |
| Leaflet marker | Fix ikon marker default (import PNG + mergeOptions) agar jalan di bundler Vite. |
| Service worker | Registrasi `/mobile/sw.js` dinonaktifkan (tidak relevan di APK). |
| `detectBiometric()` / `tryBiometric()` | Pakai `BiometricAuth.checkBiometry()` + `authenticate()`, dijaga `isNativePlatform()`. |
| `checkAppVersion()` + `applyLiveUpdate()` | Cek versi native (banner update APK) + bundle OTA (download & swap tanpa reinstall). |
| `notifyAppReady()` | Dipanggil di `mounted()` — anti-rollback Capgo. |
| `startGps()` / `stopGps()` | Pakai background watcher (foreground service) saat di APK, fallback `navigator.geolocation` di browser. Data `bgWatcherId`. |
| `registerPushNotifications()` | Register izin + token FCM ke `POST /api/v1/device-tokens`, listener notifikasi masuk/tap. |

---

## C. File yang DIUBAH — Backend (Laravel)

| File | Perubahan |
|---|---|
| `config/mobile.php` | Pisahkan **NATIVE** (`native_version`, `download_url`, `min_native_version`, `update_required`) vs **BUNDLE** (`bundle_version`, `bundle_url`). |
| `app/Http/Controllers/Api/V1/AppVersionController.php` | `show()` kembalikan info native + bundle. Tambah `bundle()` untuk serve file zip dari `storage/app/bundles/{v}.zip`. |
| `routes/api.php` | Tambah route `GET /api/v1/app/bundle/{version}` (publik, throttle). |
| `.env.example` | Tambah var: `MOBILE_APP_MIN_VERSION`, `MOBILE_BUNDLE_VERSION`, `MOBILE_BUNDLE_URL`. |

---

## D. File BARU — Dokumentasi (root project)

| File | Keterangan |
|---|---|
| `BUNDLE_OTA_SETUP.md` | Cara rilis bundle update (live update tanpa install ulang): build, zip, deploy, naikkan versi. |
| `CHECKLIST_FIRST_APK_BUILD.md` | Checklist build APK OTA-ready pertama kali: install, permission, Firebase, build, test. |
| `storage/app/bundles/.gitkeep` | Folder tempat menaruh bundle zip untuk OTA. |

---

## E. Konsep Penting: Kapan Wajib APK Baru vs Cukup Live Update

**Patokan sederhana untuk tim:**

- **Cukup BUNDLE UPDATE** (tanpa install ulang) → kalau perubahan **hanya di `mobile/src/`**: desain/CSS, layout, teks, alur layar, logika Vue, perbaikan bug JS. → Naikkan `MOBILE_BUNDLE_VERSION` + upload zip.

- **WAJIB APK BARU** (install ulang) → kalau menyentuh **lapisan native**: tambah/hapus plugin, ubah permission `AndroidManifest.xml`, ganti ikon/splash, upgrade Capacitor/Android SDK, atau apa pun di folder `android/`. → Naikkan `MOBILE_APP_VERSION` + `versionCode`.

---

## F. Arsitektur (sesuai dokumen deployment)

- APK memakai **bundle lokal** di dalam APK — **BUKAN** `server.url` ke web (kecuali dev live-reload). App bisa offline.
- Semua data lewat API: `https://fsm.indomotorlestari.com/api/v1/...`
- Kontrak API **tidak berubah** — backend Laravel yang sudah ada tetap dipakai apa adanya.
- Auth pakai localStorage: token, user, PIN per-email, flag biometric.

---

## G. Yang Sudah Selesai vs Sisa Test

**Selesai (kode & build):**
- ✅ Migrasi PWA → Vue 3 + Vite SFC
- ✅ Capacitor Android + APK berhasil di-build
- ✅ 5 plugin native terpasang (biometric, Capgo OTA, background GPS, FCM, geolocation)
- ✅ Backend module update (native + bundle)
- ✅ Permission AndroidManifest + google-services.json (dipasang user)

**Sisa test fungsional di device (oleh tim):**
- ⬜ Login + unlock biometric
- ⬜ GPS tracking saat app di-minimize (notif "FSM Teknisi aktif")
- ⬜ Terima push notification FCM
- ⬜ Test bundle OTA (naikkan `MOBILE_BUNDLE_VERSION`, upload zip, app auto-reload)
