# Business Requirements Document (BRD)

## Field Service Management (FSM) v1.0

**Versi:** 1.0 (draft)  
**Fokus rilis:** Live Tracking & Job Dispatch (MVP)  
**Status:** Siap ditinjau sebelum masuk System Flow dan ERD

---

## 1. Latar Belakang

Operasional pemasangan kaca film membutuhkan koordinasi antara sistem penjualan, koordinator, teknisi lapangan, dan customer. Tanpa satu platform, informasi jadwal, penugasan, posisi teknisi, serta status pekerjaan tersebar dan sulit dipantau secara real time.

FSM dibangun sebagai platform operasional teknisi lapangan. Rilis awal memprioritaskan penugasan pekerjaan dan live tracking; modul seperti checklist, bukti foto, KPI, dan optimasi rute akan ditambahkan pada fase berikutnya.

## 2. Tujuan Bisnis

- Mempercepat penugasan pemasangan kepada teknisi.
- Memberikan visibilitas status pekerjaan kepada koordinator.
- Memberi customer informasi perjalanan teknisi secara aman melalui tautan tracking.
- Mengurangi komunikasi manual untuk menanyakan lokasi dan progres pemasangan.
- Menyediakan fondasi yang dapat berkembang menjadi platform FSM penuh.

## 3. Ruang Lingkup MVP

### Termasuk

- Sinkronisasi atau pengambilan transaksi siap dijadwalkan dari sistem penjualan.
- Pembuatan jadwal dan assignment teknisi oleh koordinator.
- Penerimaan atau penolakan tugas oleh teknisi.
- Notifikasi penugasan kepada teknisi.
- Perubahan status pekerjaan dari penerimaan hingga selesai.
- Live GPS tracking saat teknisi berstatus `On The Way`.
- Halaman tracking customer berbasis tautan unik.
- Dashboard monitoring koordinator dan histori status/tracking.

### Tidak termasuk pada MVP

- Checklist pemasangan, foto sebelum/sesudah, dan tanda tangan digital.
- Perhitungan ETA otomatis, geofencing, dan optimasi rute.
- Penilaian KPI dan analitik lanjutan.
- Inventori, QC, warranty, dan absensi.

## 4. Aktor dan Tanggung Jawab

| Aktor | Tanggung jawab utama |
|---|---|
| Sales / Sistem Penjualan | Membuat transaksi, customer, alamat, produk, dan invoice yang siap dijadwalkan. |
| Koordinator | Menjadwalkan, menetapkan teknisi, memantau pekerjaan, dan menangani perubahan penugasan. |
| Teknisi | Menanggapi assignment, memperbarui status pekerjaan, dan mengirim lokasi saat perjalanan. |
| Customer | Menerima informasi dan melihat posisi teknisi melalui tautan tracking. |
| Administrator | Mengelola master data dan konfigurasi sistem. |

## 5. Alur Bisnis Utama

1. Sales mencatat transaksi dalam sistem penjualan dengan status siap dijadwalkan.
2. Koordinator memilih transaksi, menentukan tanggal/jam, lokasi, catatan, dan teknisi.
3. FSM membuat assignment berstatus `Waiting Acceptance` dan mengirim push notification; WhatsApp dapat digunakan bila diaktifkan.
4. Teknisi memilih `Accept` atau `Reject`.
5. Jika diterima, assignment berstatus `Accepted`; customer menerima konfirmasi jadwal.
6. Teknisi menekan `Start Trip`; status menjadi `On The Way`, tracking GPS aktif, dan link tracking customer dikirim/diaktifkan.
7. Customer melihat posisi teknisi, tujuan, dan status perjalanan melalui tautan unik.
8. Teknisi menekan `Arrived`, lalu melanjutkan status `Installation` dan akhirnya `Finished`.
9. Saat selesai, link customer dinonaktifkan/expired dan seluruh jejak status tersimpan.

## 6. Status Pekerjaan dan Aturan Transisi

| Status | Arti | Aksi yang diizinkan |
|---|---|---|
| `Draft` | Jadwal belum dikirim kepada teknisi. | Edit, assign, kirim assignment, batal. |
| `Waiting Acceptance` | Menunggu respons teknisi. | Accept, reject, reassign, reschedule, batal. |
| `Accepted` | Teknisi menyetujui tugas. | Start Trip, reassign, reschedule, batal. |
| `On The Way` | Teknisi sedang menuju lokasi; GPS aktif. | Arrived, laporkan kendala, batal sesuai otorisasi. |
| `Arrived` | Teknisi sudah berada di lokasi. | Start Installation, laporkan kendala. |
| `Installation` | Pekerjaan pemasangan sedang dilakukan. | Finish, laporkan gagal/kendala. |
| `Finished` | Pekerjaan selesai. | Tidak ada transisi normal. |
| `Rejected` | Assignment ditolak teknisi. | Reassign, reschedule, batal. |
| `Cancelled` | Pekerjaan dibatalkan. | Tidak ada transisi normal. |
| `Failed` | Pekerjaan tidak dapat diselesaikan. | Reschedule atau buat assignment baru sesuai kebijakan. |

`Rejected`, `Cancelled`, dan `Failed` wajib menyimpan alasan serta pengguna/waktu yang melakukan perubahan. Transisi status harus divalidasi oleh backend; frontend hanya menampilkan aksi yang sesuai status dan peran pengguna.

## 7. Kebutuhan Fungsional

### 7.1 Penjadwalan dan Dispatch

- Koordinator dapat melihat transaksi siap dijadwalkan.
- Koordinator dapat membuat, mengubah, membatalkan, dan menjadwal ulang pekerjaan.
- Satu pekerjaan MVP memiliki satu teknisi utama; kemungkinan multi-teknisi dicatat sebagai kebutuhan fase lanjutan.
- Sistem harus menyimpan referensi invoice, customer, alamat, produk, jadwal, teknisi, dan catatan pekerjaan.

### 7.2 Aplikasi Teknisi

- Teknisi hanya dapat melihat pekerjaan yang menjadi assignment-nya.
- Teknisi dapat menerima atau menolak beserta alasan penolakan.
- Teknisi dapat menjalankan perubahan status yang diizinkan.
- Saat `On The Way`, aplikasi meminta dan mengirim lokasi secara berkala.
- Aplikasi tetap menyimpan antrian lokasi/status secara lokal ketika koneksi sementara hilang, lalu mengirimkannya kembali saat koneksi tersedia.

### 7.3 Tracking Customer

- Customer mendapat tautan unik tanpa perlu login.
- Tautan hanya menunjukkan data minimum: status, posisi teknisi saat perjalanan, dan lokasi tujuan.
- Tautan aktif mulai `On The Way` dan berakhir pada `Finished`, `Cancelled`, atau waktu kedaluwarsa yang ditentukan.
- Tautan tidak boleh menampilkan nomor telepon teknisi, data assignment lain, atau histori lokasi lengkap.

### 7.4 Monitoring dan Notifikasi

- Koordinator dapat memantau daftar pekerjaan berdasarkan tanggal dan status.
- Koordinator dapat melihat posisi teknisi yang sedang `On The Way` secara real time.
- Sistem mengirim notifikasi assignment, perubahan jadwal, pembatalan, dan pembaruan penting.
- Pengiriman push, WhatsApp, dan email dilakukan asinkron melalui queue.

### 7.5 Audit dan Histori

- Setiap perubahan status menyimpan status lama/baru, waktu, pelaku, dan alasan bila relevan.
- Titik lokasi disimpan secara terkontrol untuk kebutuhan histori dan tidak disimpan setiap pembaruan GPS mentah.
- Koordinator dapat melihat histori status dan perjalanan untuk pekerjaan yang berwenang diaksesnya.

## 8. Aturan Operasional

- Customer menerima konfirmasi setelah assignment diterima, tetapi link tracking aktif dan dikirim ketika teknisi memulai perjalanan.
- Teknisi tidak dapat memulai perjalanan sebelum menerima assignment.
- GPS hanya dikumpulkan selama status `On The Way`, kecuali ada kebijakan eksplisit pada fase mendatang.
- Perubahan jadwal atau reassignment setelah `Accepted` harus memicu notifikasi baru kepada teknisi terdampak.
- Tugas yang tidak direspons hingga batas waktu masuk daftar eskalasi koordinator.
- Hanya koordinator/administrator yang dapat membatalkan atau melakukan reassignment, kecuali kebijakan khusus ditetapkan kemudian.

## 9. Integrasi dan Batasan

- Sistem penjualan menjadi sumber transaksi awal; FSM tidak menggantikan proses penjualan pada MVP.
- Mekanisme integrasi (API, database read-only, atau impor) akan diputuskan pada System Flow.
- Nomor invoice dari sistem penjualan harus dapat menjadi referensi unik pada FSM.
- Push notification memerlukan perangkat teknisi yang terdaftar dan izin notifikasi aktif.
- Akurasi/background GPS bergantung pada izin perangkat, jaringan, serta kebijakan penghemat baterai Android.

## 10. Indikator Keberhasilan MVP

- Koordinator dapat mengirim assignment dan melihat respons teknisi.
- Customer dapat membuka tautan tracking saat teknisi berangkat.
- Perubahan status dan posisi aktif terlihat pada dashboard tanpa refresh manual yang berarti.
- Riwayat pekerjaan dan status dapat ditelusuri untuk setiap invoice/assignment.
- Kegagalan pengiriman notifikasi tidak menghambat penyimpanan jadwal atau perubahan status.

## 11. Keputusan yang Perlu Dikonfirmasi Sebelum System Flow

1. Cara integrasi sistem penjualan: API, akses database read-only, atau impor file.
2. Batas waktu respons assignment dan aturan eskalasinya.
3. Siapa yang boleh mengubah status tertentu atau membatalkan pekerjaan.
4. Masa aktif default tautan tracking setelah pekerjaan berakhir.
5. Kebijakan retensi histori lokasi dan aksesnya.

