# Setup Bundle OTA (Capgo Self-Hosted)

Setelah APK "OTA-ready" beredar, perubahan tampilan/UI (`mobile/src/`) bisa dirilis tanpa install ulang APK. Alurnya:

> **Cara termudah: jalankan script rilis satu-perintah**
> [`release-ota.ps1`](release-ota.ps1) di root repo. Script otomatis: build web bundle,
> zip dengan format yang benar, menentukan versi berikutnya, memperbarui `.env` lokal,
> lalu menampilkan langkah upload ke VPS.
>
> ```powershell
> powershell -ExecutionPolicy Bypass -File .\release-ota.ps1
> ```
>
> (Opsi: `-Version 7` untuk memaksa nomor versi, `-NoEnvUpdate` agar `.env` lokal tidak diubah.)

1. **Build bundle baru:**

```bash
cd mobile
npm run build                          # hasilkan dist/ terbaru
```

2. **Zip isi folder `dist/` (index.html harus di root zip, bukan di dalam subfolder):**

> PENTING: JANGAN pakai `Compress-Archive` di Windows — hasil zip-nya menyimpan nama
> entry dengan backslash (`assets\index.js`) sehingga bundle gagal diekstrak di Android.
> Gunakan `tar` bawaan Windows (bsdtar) atau `zip` di Linux/Mac.

Windows PowerShell:
```powershell
tar -a -c -f bundles\6.zip -C mobile\dist index.html assets
```

Linux/Mac:
```bash
cd mobile/dist && zip -r ../../bundles/6.zip index.html assets && cd ../..
```

> Angka `6` = versi bundle baru (naikkan tiap rilis).

3. **Simpan zip di Laravel storage:**

```bash
mv bundles\6.zip storage\app\bundles\6.zip
```

Atau via FTP/deploy script taruh langsung di `storage/app/private/bundles/`.

> Penting: Laravel 12 memindahkan root disk `local` ke `storage/app/private`.
> File zip WAJIB di `storage/app/private/bundles/{version}.zip` — bukan `storage/app/bundles/`.
> Kalau ditaruh di tempat lama, endpoint akan tetap 404 karena `Storage::disk('local')`
> mengarah ke folder `private`.

4. **Perbarui `.env` Laravel:**

```env
MOBILE_BUNDLE_VERSION=6
# MOBILE_BUNDLE_URL dikosongkan — endpoint /api/v1/app/bundle/6 otomatis serve dari storage.
```

5. **Restart Laravel (queue/worker kalau pakai):**

```bash
php artisan config:clear
php artisan queue:restart  # kalau pakai queue
```

6. **Teknisi buka app → otomatis download bundle baru → app reload dengan UI baru.**

---

## Catatan penting

- **Hanya untuk perubahan `mobile/src/`** (tampilan/UI/logika Vue). Kalau tambah plugin native, ubah permission, atau sentuh `android/`, itu wajib APK baru (native version).
- Bundle rollback otomatis jika app gagal dibuka setelah update (safety Capgo `notifyAppReady()`).
- Endpoint `GET /api/v1/app/bundle/{version}` ada di `AppVersionController@bundle`, publik (tanpa auth), throttle `public-tracking`.
- File bundle disimpan di `storage/app/private/bundles/{version}.zip` — backup file lama untuk rollback manual kalau perlu.

---

## Troubleshooting

**Bundle tidak ter-download:**
- Cek `storage/app/private/bundles/{version}.zip` ada dan bisa diakses via `GET /api/v1/app/bundle/{version}`.
- Cek `MOBILE_BUNDLE_VERSION` di `.env` cocok dengan file yang ada.

**App crash setelah update:**
- Capgo akan rollback otomatis ke bundle sebelumnya dalam ~30 detik.
- Hapus bundle bermasalah, turunkan `MOBILE_BUNDLE_VERSION` di `.env` ke versi stabil terakhir.

**Perubahan tidak muncul di app:**
- User tutup app paksa (force-close) lalu buka lagi — update dicek saat `mounted()`.
- Cek localStorage user: `fsm_bundle_version` (angka terakhir yang terpasang). Kalau masih nilai lama, berarti download belum berhasil atau bundle_url salah.
