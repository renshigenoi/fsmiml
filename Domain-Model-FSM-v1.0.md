# Domain Model

## Field Service Management (FSM) v1.0

**Versi:** 1.0 (draft)  
**Tujuan:** Menetapkan bahasa bisnis bersama untuk MVP Live Tracking & Job Dispatch sebelum Functional Requirement, ERD, dan API dirancang.

---

## 1. Prinsip Bahasa Bisnis

- Istilah resmi untuk pekerjaan lapangan adalah **Work Order (WO)**, bukan “job”, “pemasangan”, atau “order” secara bergantian.
- **Sales Order** adalah transaksi dari sistem penjualan. Ia dapat menjadi sumber Work Order, tetapi bukan Work Order itu sendiri.
- **Assignment** adalah penugasan satu Work Order kepada teknisi; ia menyimpan keputusan dispatch dan respons teknisi.
- **Tracking** melekat pada Work Order/Assignment, bukan khusus pada jenis pemasangan tertentu.
- Status operasional utama berada pada Work Order. Assignment menyimpan status respons penugasan bila diperlukan.

## 2. Entitas Inti

| Entitas | Definisi bisnis | Peran pada MVP |
|---|---|---|
| Sales Order | Transaksi penjualan dari sistem existing. | Sumber data customer, produk, alamat, dan invoice. |
| Customer | Pihak yang menerima layanan lapangan. | Pemilik lokasi pekerjaan dan penerima tautan tracking. |
| Work Order | Perintah kerja lapangan yang dapat dieksekusi. | Entitas pusat untuk jadwal, status, customer, dan referensi Sales Order. |
| Assignment | Penugasan Work Order kepada seorang teknisi. | Menyimpan teknisi, waktu penugasan, keputusan accept/reject, dan alasan. |
| Technician | Pengguna lapangan yang menjalankan Work Order. | Menerima assignment dan mengirim pembaruan status/lokasi. |
| Service Location | Lokasi tujuan layanan. | Menyimpan alamat dan koordinat tujuan Work Order. |
| Tracking Session | Sesi live tracking yang dibatasi waktu. | Aktif ketika teknisi berstatus `On The Way`. |
| Tracking Point | Sampel lokasi GPS yang tervalidasi. | Histori perjalanan yang disimpan terkontrol. |
| Tracking Token | Kredensial publik terbatas untuk customer tracking page. | Mengizinkan customer melihat sesi tracking yang relevan. |
| Work Order Status History | Catatan perubahan status Work Order. | Audit proses dan sumber laporan durasi. |
| Notification | Catatan komunikasi sistem ke pengguna. | Menelusuri push, WhatsApp, atau email yang diantrikan/dikirim. |
| User | Akun pengguna sistem. | Mewakili koordinator, teknisi, dan administrator beserta perannya. |

## 3. Hubungan Konseptual

```text
Sales Order ── menghasilkan ──► Work Order ── ditugaskan melalui ──► Assignment
                                      │                                  │
                                      ├── untuk ──► Customer              └── kepada ──► Technician
                                      ├── di ───► Service Location
                                      ├── memiliki ──► Work Order Status History
                                      └── memiliki ──► Tracking Session ── menyimpan ──► Tracking Point
                                                               │
                                                               └── diakses terbatas oleh ──► Tracking Token

User ── menerima ──► Notification
```

## 4. Siklus Hidup Konsep Utama

### Sales Order ke Work Order

Sales Order berasal dari sistem penjualan dan dapat dipilih ketika siap dijadwalkan. FSM membuat Work Order dengan referensi invoice/sales order yang tetap dapat ditelusuri. Pada MVP, perubahan transaksi penjualan setelah Work Order dibuat mengikuti aturan sinkronisasi yang akan diputuskan pada desain integrasi.

### Work Order dan Assignment

Work Order dapat berada pada `Draft`, kemudian ditugaskan kepada teknisi melalui Assignment. MVP memakai satu teknisi utama per Assignment. Bila teknisi menolak, koordinator membuat reassignment atau reschedule sehingga catatan keputusan sebelumnya tidak hilang.

### Tracking Session

Tracking Session dapat dibuat ketika assignment dibuat, tetapi masih tidak aktif. Ia aktif saat Work Order menjadi `On The Way`; sesi ditutup ketika pekerjaan tiba, selesai, dibatalkan, atau gagal sesuai aturan status. Lokasi terkini bersifat sementara di Redis, sedangkan Tracking Point adalah histori yang dipersistenkan ke PostgreSQL.

### Customer Tracking

Tracking Token tidak sama dengan akun customer. Token memberikan akses baca terbatas pada satu sesi tracking dan memiliki masa berlaku. Token tidak boleh digunakan untuk mengubah data maupun melihat work order lain.

## 5. Istilah yang Tidak Boleh Dipertukarkan

| Jangan gunakan sebagai sinonim | Gunakan istilah resmi | Alasan |
|---|---|---|
| Order / Job / Pemasangan | Work Order | Satu istilah utama untuk pekerjaan lapangan lintas jenis layanan. |
| Penugasan dan pekerjaan | Assignment vs Work Order | Assignment adalah proses/personel; Work Order adalah pekerjaan bisnisnya. |
| Lokasi live dan histori GPS | Current Location vs Tracking Point | Data realtime sementara berbeda dari rekam jejak persisten. |
| Link customer dan login customer | Tracking Token | MVP memakai akses publik terbatas, bukan autentikasi customer. |

## 6. Aturan Domain Awal

- Satu Work Order harus memiliki customer dan service location sebelum dapat dikirim sebagai Assignment.
- Setiap Assignment harus ditujukan kepada tepat satu teknisi utama pada MVP.
- Hanya teknisi pada Assignment aktif yang boleh menerima tugas, memulai perjalanan, atau mengirim lokasi.
- Tracking Session hanya boleh aktif untuk Work Order yang berstatus `On The Way`.
- Tracking Point harus terkait dengan Tracking Session yang valid.
- Setiap transisi Work Order harus menghasilkan Work Order Status History.
- Status terminal Work Order adalah `Finished`, `Cancelled`, dan `Failed`.
- Work Order dapat merepresentasikan pemasangan, klaim garansi, servis ulang, atau survey; jenis pekerjaan adalah atribut/domain yang dapat diperluas, bukan asumsi pada modul Tracking.

## 7. Batas MVP dan Ekstensi Masa Depan

Pada MVP, Work Order berfokus pada dispatch dan live tracking. Entitas lanjutan seperti checklist, evidence photo, signature, material usage, warranty claim, dan technician team tidak dibuat sebagai inti awal, tetapi akan ditautkan ke `work_order_id` saat modulnya dibangun.

## 8. Dampak terhadap Tahap Berikutnya

- **FR:** setiap kebutuhan diberi kode dan dipetakan ke entitas/aktor di atas.
- **NFR:** menetapkan target performa realtime, keamanan token, retensi lokasi, dan availability.
- **ERD:** menerjemahkan entitas dan hubungan ini menjadi tabel, primary key, foreign key, indeks, dan aturan integritas.
- **API:** menggunakan resource yang konsisten, misalnya `/work-orders`, `/assignments`, dan `/tracking-sessions`.

