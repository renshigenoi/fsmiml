# Checklist Build APK OTA-Ready — Sekali Saja

Setelah semua plugin native terpasang (Capgo, background GPS, FCM), tim **wajib build + install ulang APK sekali** agar OTA bisa jalan. Setelah APK ini beredar, perubahan UI berikutnya bisa lewat bundle update tanpa install ulang lagi.

## 1. Install dependency & build web bundle

```bash
cd D:\laragon\www\tech-iml\mobile
npm install                # install semua plugin (termasuk Capgo, FCM, background GPS)
npm run build              # hasilkan dist/ pertama kali
```

## 2. Setup Android platform

```bash
npx cap add android        # buat folder android/ (sekali saja)
npx cap sync android
```

## 3. Tambahkan izin di `android/app/src/main/AndroidManifest.xml`

Paste setelah tag `<manifest>`:

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
<!-- Kamera & galeri (foto selesai pemasangan) -->
<uses-permission android:name="android.permission.CAMERA" />
<uses-permission android:name="android.permission.READ_MEDIA_IMAGES" />
<!-- Auto-download & pasang update APK native -->
<uses-permission android:name="android.permission.REQUEST_INSTALL_PACKAGES" />
```

> **Catatan `REQUEST_INSTALL_PACKAGES`:** izin ini yang membuat app bisa memicu dialog installer Android otomatis setelah APK selesai diunduh. Android tetap menampilkan dialog "Install" (tidak bisa silent install) — user tap "Install" 1x. Google Play membatasi izin ini; karena APK ini didistribusikan manual (bukan lewat Play Store), aman digunakan.

> **CATATAN (plugin baru `@capacitor/camera`):** karena ada tambahan plugin native, tim **wajib rebuild + install ulang APK** sekali lagi (bukan cukup bundle OTA). Jalankan `npm install` lalu `npx cap sync android` sebelum build.

## 4. Setup Firebase (untuk push notification)

1. Buat project di [Firebase Console](https://console.firebase.google.com).
2. Tambahkan app Android dengan package name: **`com.iml.fsm.technician`**.
3. Download `google-services.json` → taruh di `android/app/google-services.json`.
4. Pastikan `android/build.gradle` & `android/app/build.gradle` punya plugin Google Services (biasanya Capacitor sudah menyiapkan; kalau belum, ikuti panduan Firebase).

## 5. Buka di Android Studio & build APK

```bash
npx cap open android
```

Di Android Studio:
- **Build > Build APK(s)** → hasilkan `.apk` untuk testing (unsigned), atau
- **Build > Generate Signed Bundle/APK** → build release (signed) untuk distribusi.

> **PENTING:** Simpan keystore signing dengan aman & backup. Kehilangan keystore = tidak bisa update APK yang sudah beredar.

## 6. Install APK ke device/emulator

Test bahwa:
- Login, biometric, GPS tracking, dan notifikasi FCM jalan.
- App bisa cek update (`GET /api/v1/app/version`).

## 7. Rilis bundle update pertama (opsional — untuk test OTA)

Setelah APK beredar, test mekanisme OTA:

```bash
npm run build
# Zip isi dist/ jadi bundles/2.zip (index.html di root zip)
# Simpan ke storage/app/bundles/2.zip di server Laravel
```

Edit `.env` Laravel:

```env
MOBILE_BUNDLE_VERSION=2
```

Teknisi buka app → otomatis download bundle 2 → app reload dengan UI baru tanpa install ulang.

---

## Catatan

Setelah checklist ini selesai, **perubahan UI berikutnya cukup bundle update** (naikkan `MOBILE_BUNDLE_VERSION` + upload zip) — **tidak perlu install ulang APK**. Build APK baru hanya kalau:
- Tambah/hapus plugin Capacitor
- Ubah permission di `AndroidManifest.xml`
- Upgrade Capacitor SDK atau Android SDK
- Ganti ikon/splash screen
