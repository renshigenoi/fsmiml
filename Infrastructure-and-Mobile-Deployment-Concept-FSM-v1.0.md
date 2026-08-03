# Infrastructure and Mobile Deployment Concept

## Field Service Management (FSM) v1.0

**Versi:** 1.0 (draft)  
**Status:** Keputusan awal untuk environment development, MVP/internal pilot, dan persiapan production.

---

## 1. Tujuan Dokumen

Dokumen ini merangkum keputusan infrastruktur dan distribusi aplikasi yang telah dibahas untuk FSM v1.0. Fokusnya adalah deployment awal yang sederhana, aman, dan cukup untuk MVP Live Tracking & Job Dispatch.

## 2. Stack yang Dikunci

| Layer | Keputusan |
|---|---|
| Control panel | aaPanel |
| Web server | Nginx melalui aaPanel |
| Backend API | Laravel 12 |
| PHP | PHP 8.3.x, patch terbaru yang tersedia |
| Database | PostgreSQL; nama database `fsm_db` |
| Cache, queue, realtime state | Redis |
| WebSocket | Laravel Reverb di belakang reverse proxy Nginx |
| Web admin | Vue 3 + Vite |
| Customer tracking | Vue web page/SPA yang ringan |
| Mobile teknisi | Vue 3 + Capacitor Android |

## 3. PHP dan Ekstensi

Gunakan PHP 8.3 karena Laravel 12 mendukungnya dan masa security support-nya lebih panjang daripada PHP 8.2.

Ekstensi minimum:

```text
pdo_pgsql
pgsql
redis
mbstring
xml
curl
zip
bcmath
intl
opcache
pcntl
```

`pcntl` digunakan oleh proses CLI Laravel, terutama queue worker. Konfigurasi PHP web tidak boleh menonaktifkan fungsi yang dibutuhkan worker CLI.

## 4. PostgreSQL

Target ideal adalah menggunakan minor release PostgreSQL terbaru dalam major version yang dipilih.

Kondisi saat ini di aaPanel:

```text
PostgreSQL 16.10 tersedia
```

Keputusan praktis:

- PostgreSQL 16.10 boleh digunakan untuk development dan memulai MVP bila itu satu-satunya opsi aaPanel.
- Sebelum production, lakukan minor upgrade ke versi 16 terbaru yang tersedia saat itu, atau gunakan managed database/container terpisah yang dapat dipatch rutin.
- Jangan menimpa binary PostgreSQL yang dikelola aaPanel secara manual; gunakan mekanisme upgrade yang didukung panel atau migrasikan dengan prosedur terencana.
- Siapkan PostGIS sebagai ekstensi masa depan untuk geofencing, radius, nearest technician, dan analisis lokasi. PostGIS belum wajib diaktifkan pada MVP.

## 5. Kapasitas Server Awal

Spesifikasi saat ini:

```text
CPU: 2 core
RAM: 8 GB
Storage bebas: sekitar 40 GB (HDD)
```

Spesifikasi tersebut cukup untuk development dan internal pilot/MVP dengan kira-kira 20–50 teknisi aktif, dengan asumsi beban server tidak bercampur dengan aplikasi berat lain.

Keterbatasan utama adalah storage HDD 40 GB:

- Tracking Point, log aplikasi, failed jobs, dan backup database akan bertambah seiring waktu.
- Modul foto bukti pemasangan pada fase berikutnya akan menaikkan kebutuhan storage secara signifikan.
- SSD lebih baik untuk PostgreSQL dan beban realtime dibanding HDD.

Target sebelum produksi atau ketika penggunaan bertambah:

```text
Storage kosong: minimal 80–100 GB
Media: SSD lebih diutamakan
Backup: lokasi terpisah dari server aplikasi
```

## 6. Struktur Domain dan Subdomain

Gunakan dua subdomain untuk menjaga deployment tetap sederhana:

```text
fsm.subdomain.com
├── Vue Web Admin
├── Laravel application
├── /api/v1/...          Laravel REST API
└── WebSocket/Reverb     melalui reverse proxy Nginx (WSS)

track.subdomain.com
└── Customer Tracking Page
```

Catatan:

- Aplikasi mobile tidak membutuhkan subdomain terpisah; ia memanggil `https://fsm.subdomain.com/api/v1`.
- Karena customer tracking berada di origin berbeda, Laravel perlu mengonfigurasi CORS secara ketat hanya untuk `https://track.subdomain.com` dan domain yang benar-benar dibutuhkan.
- WebSocket harus memakai `wss://` pada production dan hanya mengizinkan channel/token yang sah.

## 7. Keamanan Server Dasar

- Semua aplikasi dan panel menggunakan HTTPS dengan sertifikat valid.
- Akses aaPanel dibatasi ke IP kantor, VPN, atau daftar IP tepercaya; jangan dibiarkan terbuka untuk semua internet.
- Publik hanya membuka port HTTP/HTTPS (`80` dan `443`) sesuai kebutuhan.
- PostgreSQL (`5432`) dan Redis (`6379`) tidak diekspos ke internet.
- SSH dibatasi dengan key authentication, akun non-root bila memungkinkan, dan firewall.
- Aktifkan firewall serta mekanisme perlindungan brute-force seperti Fail2Ban bila tersedia.
- Perbarui OS, aaPanel, PHP, PostgreSQL, Redis, dan package aplikasi secara berkala dengan prosedur backup terlebih dahulu.
- Simpan credential database, FCM, WhatsApp, SMTP, dan signing key hanya di environment/secret storage; jangan masukkan ke Git.

## 8. Service yang Harus Berjalan

Selain Nginx dan PHP-FPM, production membutuhkan proses berikut yang diawasi dan auto-restart:

```text
Laravel Queue Worker
Laravel Scheduler
Laravel Reverb
Redis
PostgreSQL
```

Queue worker, scheduler, dan Reverb harus dijalankan sebagai service terpisah (misalnya systemd/Supervisor), bukan melalui request web atau tab terminal biasa.

## 9. Strategi Capacitor Mobile

Aplikasi teknisi menggunakan bundle lokal di dalam APK/AAB:

```text
Vue Mobile
→ npm run build
→ npx cap sync
→ build APK/AAB
```

Keputusan ini dipilih karena lebih andal untuk:

- background GPS;
- push notification;
- offline queue dan retry lokasi;
- penggunaan plugin native;
- keamanan aplikasi.

Jangan menggunakan konfigurasi Capacitor yang langsung membuka URL web eksternal sebagai aplikasi production. `server.url` digunakan untuk live reload/development, bukan untuk runtime production.

## 10. Versioning dan Update APK

Setiap rilis Android memiliki dua nilai:

```text
versionCode  → angka internal yang selalu naik pada setiap rilis
versionName  → angka yang terlihat user, misalnya 1.0.0 atau 1.1.0
```

Mobile memeriksa informasi rilis saat aplikasi dibuka melalui endpoint, misalnya:

```text
GET /api/v1/mobile/releases/android
```

Respons minimum:

```json
{
  "latestVersionCode": 2,
  "minimumVersionCode": 1,
  "versionName": "1.0.1",
  "mandatory": false,
  "apkUrl": "https://fsm.subdomain.com/downloads/fsm-technician-1.0.1.apk",
  "sha256": "<checksum-file>",
  "releaseNotes": "Perbaikan tracking GPS"
}
```

Aturan update:

- Jika aplikasi masih memenuhi `minimumVersionCode`, tampilkan update sebagai opsional.
- Jika `versionCode` lebih rendah dari `minimumVersionCode`, update bersifat wajib dan aksi kerja diblokir sampai pengguna memperbarui aplikasi.
- Unduhan APK hanya dari HTTPS dan checksum diverifikasi sebelum instalasi.
- APK baru wajib memakai `applicationId` dan signing key yang sama dengan aplikasi yang sudah terpasang.
- Android normal tetap meminta tindakan/persetujuan pengguna saat menginstal update. Silent update hanya realistis untuk perangkat yang dikelola perusahaan menggunakan MDM/device owner.
- Signing keystore harus dibackup secara aman. Kehilangan signing key akan menghambat update aplikasi yang telah beredar.

## 11. Batas Perubahan Tanpa APK Baru

| Perubahan | Perlu APK/AAB baru? |
|---|---|
| Data, API, status pekerjaan, notifikasi dari server | Tidak |
| Perbaikan backend Laravel | Tidak |
| Perubahan Vue Mobile yang dibundel | Ya |
| Plugin Capacitor, izin Android, GPS background, FCM native | Ya |
| Konfigurasi Android/signing/target SDK | Ya |

Mekanisme live update untuk JavaScript dapat dievaluasi setelah MVP stabil. Ia tidak menggantikan kebutuhan rilis APK untuk perubahan native dan harus dirancang dengan kontrol keamanan/rollback yang jelas.

## 12. Prioritas Sebelum Production

1. Patch PostgreSQL/PHP/OS ke versi aman terbaru yang tersedia.
2. Konfigurasi HTTPS, firewall, panel IP allowlist, dan SSH key.
3. Jalankan Queue, Scheduler, dan Reverb sebagai service auto-restart.
4. Siapkan backup PostgreSQL harian serta uji restore.
5. Pasang monitoring CPU, RAM, disk, error log, queue gagal, dan status service.
6. Dokumentasikan signing key dan prosedur rilis APK.
7. Tetapkan retensi histori GPS, log, serta file upload sebelum modul foto diaktifkan.

