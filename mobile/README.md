# FSM Teknisi — Mobile (Vue 3 + Vite + Capacitor)

Aplikasi teknisi FSM Indo Motor Lestari, dibungkus jadi **APK Android** dengan Capacitor.
Sumber UI dimigrasi dari `resources/views/mobile/app.blade.php` (PWA lama) ke project Vue 3 + Vite ini.

> Prinsip (sesuai `Infrastructure-and-Mobile-Deployment-Concept-FSM-v1.0.md`):
> APK memakai **bundle lokal** (`dist/`), **bukan** `server.url` ke web. Semua data lewat `GET/POST https://<server>/api/v1/...`.

---

## 1. Prasyarat (di PC Windows kamu)

- **Node.js 20+** dan npm — cek: `node -v`
- **Android Studio** (termasuk Android SDK + platform-tools) — untuk build & signing APK
- **JDK 17** (biasanya sudah ikut Android Studio)
- Backend Laravel FSM jalan dan bisa diakses dari HP/emulator (Laragon)

---

## 2. Konfigurasi API base URL

Panggilan API TIDAK boleh pakai path relatif di dalam APK (origin di APK = `capacitor://localhost`).
Base URL diambil dari `VITE_API_BASE` di file `.env` (lihat `.env.example`).

```bash
copy .env.example .env      # Windows (atau: cp .env.example .env)
```

Isi `.env`:

| Skenario | VITE_API_BASE |
|---|---|
| Emulator Android (Laragon di PC yang sama) | `http://10.0.2.2:8000` |
| HP fisik satu WiFi dengan PC | `http://<IP-LAN-PC>:8000` (mis. `http://192.168.1.10:8000`) |
| Production | `https://fsm.subdomain.com` |

> `10.0.2.2` adalah alias khusus emulator Android untuk `localhost` PC host.
> Untuk HP fisik, jalankan Laravel dengan `php artisan serve --host=0.0.0.0` agar bisa diakses dari LAN.

---

## 3. Install & jalankan (web dev)

```bash
cd mobile
npm install
npm run dev          # buka http://localhost:5174 di browser desktop untuk cek cepat UI
```

---

## 4. Build web assets → sinkron ke Android → buka Android Studio

Pertama kali (menambahkan platform Android):

```bash
npm run build                 # hasilkan dist/
npx cap add android           # buat folder android/ (sekali saja)
npx cap sync android          # salin dist/ + plugin ke android/
npx cap open android          # buka di Android Studio
```

Sesudah itu, tiap ada perubahan kode Vue cukup:

```bash
npm run android               # = build + cap sync android + cap open android
```

Di Android Studio: **Run** ke emulator/HP, atau **Build > Build APK(s)** untuk menghasilkan `.apk`.

---

## 5. Izin & plugin Android

Plugin yang sudah didaftarkan di `package.json`:

- `@capacitor/app` — tombol back, exit, info versi (dipakai cek update).
- `@capacitor/geolocation` — akses GPS (fallback saat di browser).
- `@aparajita/capacitor-biometric-auth` — unlock aplikasi via sidik jari / Face (biometric).
- `@capgo/capacitor-updater` — **live update (OTA)**: tukar UI tanpa install ulang APK.
- `@capacitor-community/background-geolocation` — **GPS background**: tracking tetap jalan saat app di-minimize.
- `@capacitor/push-notifications` — **FCM push**: notifikasi WO baru/update.

Setelah `cap add android`, tambahkan izin di `android/app/src/main/AndroidManifest.xml`:

```xml
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
<uses-permission android:name="android.permission.USE_BIOMETRIC" />
<!-- Background GPS -->
<uses-permission android:name="android.permission.ACCESS_BACKGROUND_LOCATION" />
<uses-permission android:name="android.permission.FOREGROUND_SERVICE" />
<uses-permission android:name="android.permission.FOREGROUND_SERVICE_LOCATION" />
<uses-permission android:name="android.permission.WAKE_LOCK" />
<!-- Push notification -->
<uses-permission android:name="android.permission.POST_NOTIFICATIONS" />
```

> **PENTING:** ketiga plugin baru (Capgo, background GPS, FCM) adalah **kode native**. Karena itu,
> saat pertama kali dipasang tim **wajib build + install ulang APK sekali**. Setelah APK "OTA-ready"
> ini beredar, perubahan UI berikutnya bisa lewat live update tanpa install ulang lagi.

### Biometric
Plugin `@aparajita/capacitor-biometric-auth` (v7). Kode di `App.vue` memanggil `BiometricAuth.checkBiometry()` untuk deteksi dan `BiometricAuth.authenticate()` untuk unlock. Hanya aktif di dalam APK; di browser di-skip.

### Background GPS
Plugin `@capacitor-community/background-geolocation`. `startGps()` otomatis pakai background watcher saat di APK (muncul notifikasi "FSM Teknisi aktif" selama tracking — wajib ada untuk foreground service Android), dan fallback ke `navigator.geolocation` saat di browser. Data lokasi tetap dikirim ke `POST /api/v1/tracking-sessions/{id}/locations` seperti biasa.

### Push notification (FCM)
Plugin `@capacitor/push-notifications`. Butuh setup **Firebase**:

1. Buat project di [Firebase Console](https://console.firebase.google.com) → tambahkan app Android dengan package `com.iml.fsm.technician`.
2. Download `google-services.json` → taruh di `android/app/google-services.json`.
3. Pastikan `android/build.gradle` & `android/app/build.gradle` punya plugin Google Services (biasanya Capacitor sudah menyiapkan; kalau belum, ikuti panduan Firebase).
4. Token FCM otomatis didaftarkan ke `POST /api/v1/device-tokens` setelah teknisi login. Backend (`FCM_DRIVER` di `.env`) yang mengirim notifikasi.

---

## 6. Versioning & update APK

Aplikasi mendukung **dua jenis versi**:

### 1. NATIVE (wajib install ulang APK)
Versi ini berlaku untuk **perubahan di lapisan native**: menambah/hapus plugin Capacitor, ubah izin `AndroidManifest.xml`, ganti ikon/splash, upgrade Capacitor SDK, atau apa pun yang menyentuh folder `android/`.

- `versionCode` (naik tiap rilis) & `versionName` diatur di `android/app/build.gradle`.
- App cek `GET /api/v1/app/version` saat dibuka. Respons:

```json
{
  "version": "1.0.1",
  "download_url": "https://.../fsm-1.0.1.apk",
  "min_version": "1.0.0",
  "update_required": false,
  "bundle_version": 5,
  "bundle_url": "https://.../bundles/5.zip"
}
```

Atur di Laravel `.env`:
```
MOBILE_APP_VERSION=1.0.1
MOBILE_APP_DOWNLOAD_URL=https://fsm.indomotorlestari.com/apk/fsm-1.0.1.apk
MOBILE_APP_MIN_VERSION=1.0.0
MOBILE_APP_UPDATE_REQUIRED=false
```

Jika `update_required=true`, teknisi **wajib** update sebelum lanjut. Jika versi native user < `min_version`, update dipaksa (tidak bisa dilewati).

### 2. BUNDLE (live update tanpa install ulang) — Capgo self-hosted
Versi ini berlaku untuk **perubahan di `mobile/src/`** saja: desain/CSS, layout, alur layar, logika Vue, perbaikan bug di JS. Mayoritas perubahan harian masuk sini. Ditenagai plugin `@capgo/capacitor-updater` dengan bundle di-host di server sendiri (bukan cloud Capgo).

**Cara rilis bundle baru (tanpa install ulang APK):**

```bash
cd mobile
npm run build                        # hasilkan dist/ terbaru
```

Zip **isi** folder `dist/` (index.html harus di root zip, bukan di dalam subfolder). Di Windows PowerShell:

```powershell
Compress-Archive -Path mobile\dist\* -DestinationPath 6.zip   # 6 = versi bundle baru
```

Taruh file zip di server Laravel: `storage/app/bundles/6.zip`, lalu naikkan versi di `.env` Laravel:

```
MOBILE_BUNDLE_VERSION=6
# MOBILE_BUNDLE_URL boleh dikosongkan — backend otomatis serve dari
# GET /api/v1/app/bundle/6  (endpoint AppVersionController@bundle)
```

Alurnya: saat teknisi buka app, `checkAppVersion()` membaca `bundle_version` dari `/api/v1/app/version`. Kalau lebih baru dari yang terpasang, `applyLiveUpdate()` men-download zip, meng-aktifkannya (`CapacitorUpdater.set`), dan app reload dengan UI baru — **tanpa install ulang**. Jika app gagal dibuka, `notifyAppReady()` yang tidak terpanggil akan memicu Capgo rollback otomatis ke bundle sebelumnya (aman).

> **Batasan:** live update HANYA untuk kode web (`src/`). Kalau kamu menambah plugin native, ubah permission, atau menyentuh `android/`, itu WAJIB APK baru (native version) — bundle update tidak cukup.

### Aturan versioning

- Simpan **keystore signing** dengan aman & backup. Kehilangan keystore = tidak bisa update APK yang sudah beredar.
- APK baru wajib `applicationId` (`com.iml.fsm.technician`) & signing key yang sama.
- **Patokan untuk tim:** perubahan cuma di `mobile/src/` → cukup **bundle update** (naikkan `MOBILE_BUNDLE_VERSION` + upload zip). Sentuh `android/`, tambah plugin, atau ubah permission → **build APK baru** (naikkan `MOBILE_APP_VERSION` + `versionCode`).

---

## 7. Struktur project

```
mobile/
├── capacitor.config.json    # appId, appName, webDir=dist (JSON murni, tanpa server.url)
├── index.html               # entry Vite
├── vite.config.js           # base './' agar aset relatif jalan di APK
├── .env.example             # contoh VITE_API_BASE
├── public/assets/images/    # icon.png, iml-logo.png
└── src/
    ├── main.js              # bootstrap Vue
    ├── App.vue              # seluruh UI + logika (migrasi dari app.blade.php)
    ├── assets/css/app.css   # import Leaflet CSS + reset
    └── composables/
        └── api.js           # API_BASE / API_V1 terpusat (dari VITE_API_BASE)
```

## Catatan migrasi

- Semua `fetch('/api/v1...')` diarahkan ke `API_V1` (absolut) dari `src/composables/api.js`.
- Leaflet dipakai sebagai modul npm (`import L from 'leaflet'`) dan diekspos `window.L` agar
  kode peta yang memakai `L.` tetap berfungsi.
- Registrasi service worker `/mobile/sw.js` dinonaktifkan (tidak relevan di dalam APK).
- Kontrak API tidak berubah — backend Laravel yang sudah ada tetap dipakai apa adanya.
