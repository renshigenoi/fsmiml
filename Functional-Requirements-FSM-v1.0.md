# Functional Requirements (FR)

## Field Service Management (FSM) v1.0 — Live Tracking & Job Dispatch

**Versi:** 1.0 (draft)  
**Dasar:** BRD, Domain Model, Business Flow, dan System Flow FSM v1.0  
**Prioritas:** Semua requirement di bawah adalah MVP kecuali ditandai lain.

---

## 1. Konvensi

- **Must**: wajib tersedia pada rilis MVP.
- **Should**: penting, tetapi dapat dijadwalkan setelah fungsi inti jika diperlukan.
- Setiap requirement nantinya menjadi dasar use case, endpoint API, acceptance test, dan backlog sprint.

## 2. Autentikasi dan Otorisasi

| ID | Prioritas | Requirement |
|---|---|---|
| FR-AUTH-001 | Must | Sistem harus mengautentikasi pengguna internal menggunakan Laravel Sanctum. |
| FR-AUTH-002 | Must | Sistem harus membatasi akses berdasarkan peran minimal: Administrator, Koordinator, dan Teknisi. |
| FR-AUTH-003 | Must | Teknisi hanya dapat melihat dan menjalankan aksi pada assignment yang ditugaskan kepadanya. |
| FR-AUTH-004 | Must | Koordinator hanya dapat menjalankan aksi dispatch, reschedule, reassignment, dan monitoring sesuai otorisasinya. |
| FR-AUTH-005 | Must | Setiap aksi perubahan data harus mencatat pengguna dan waktu pelaksanaannya. |

## 3. Referensi Sales Order dan Customer

| ID | Prioritas | Requirement |
|---|---|---|
| FR-SO-001 | Must | Sistem harus dapat menampilkan Sales Order yang siap dijadwalkan dari sistem penjualan. |
| FR-SO-002 | Must | Data minimum Sales Order yang tersedia harus mencakup nomor invoice, customer, alamat, produk/layanan, dan informasi kontak yang diizinkan. |
| FR-SO-003 | Must | Sistem harus menyimpan referensi Sales Order/invoice pada Work Order untuk kebutuhan penelusuran. |
| FR-CUS-001 | Must | Sistem harus menyimpan atau mereferensikan customer dan service location yang digunakan oleh Work Order. |
| FR-CUS-002 | Must | Koordinator harus dapat melihat detail customer dan lokasi layanan ketika membuat atau meninjau Work Order. |

## 4. Work Order dan Scheduling

| ID | Prioritas | Requirement |
|---|---|---|
| FR-WO-001 | Must | Koordinator harus dapat membuat Work Order dari Sales Order yang siap dijadwalkan. |
| FR-WO-002 | Must | Work Order harus memiliki nomor/identitas unik, referensi Sales Order, customer, service location, jenis pekerjaan, serta status. |
| FR-WO-003 | Must | Koordinator harus dapat menentukan tanggal, waktu/jendela waktu, dan catatan pekerjaan pada Work Order. |
| FR-WO-004 | Must | Koordinator harus dapat memperbarui jadwal sebelum pekerjaan berada pada status terminal, sesuai otorisasi. |
| FR-WO-005 | Must | Sistem harus memvalidasi kelengkapan data Work Order sebelum assignment dikirim. |
| FR-WO-006 | Must | Sistem harus memungkinkan Work Order dibatalkan oleh pengguna yang berwenang dengan alasan pembatalan. |
| FR-WO-007 | Should | Sistem harus dapat memfilter dan mencari Work Order berdasarkan tanggal, status, teknisi, nomor invoice, dan customer. |

## 5. Assignment dan Dispatch

| ID | Prioritas | Requirement |
|---|---|---|
| FR-ASG-001 | Must | Koordinator harus dapat memilih satu teknisi utama untuk sebuah Work Order pada MVP. |
| FR-ASG-002 | Must | Sistem harus memvalidasi bahwa teknisi aktif dan tidak memiliki jadwal bentrok sebelum assignment dibuat. |
| FR-ASG-003 | Must | Saat assignment dikirim, status Work Order harus berubah menjadi `Waiting Acceptance`. |
| FR-ASG-004 | Must | Sistem harus membuat Tracking Session yang belum aktif ketika assignment dibuat. |
| FR-ASG-005 | Must | Sistem harus mengirim push notification kepada teknisi untuk assignment baru. |
| FR-ASG-006 | Must | Teknisi harus dapat menerima (`Accept`) atau menolak (`Reject`) assignment melalui aplikasi mobile. |
| FR-ASG-007 | Must | Penolakan assignment harus mewajibkan alasan penolakan. |
| FR-ASG-008 | Must | Saat assignment diterima, status Work Order harus berubah menjadi `Accepted` dan koordinator harus diberi notifikasi. |
| FR-ASG-009 | Must | Saat assignment ditolak, status Work Order/assignment harus tercatat sebagai `Rejected` dan koordinator harus diberi notifikasi. |
| FR-ASG-010 | Must | Koordinator harus dapat melakukan reassignment atau reschedule terhadap Work Order yang ditolak atau belum dieksekusi. |
| FR-ASG-011 | Must | Sistem harus menyimpan histori setiap assignment, respons, dan alasan perubahan agar audit trail tidak hilang. |

## 6. Status Pekerjaan

| ID | Prioritas | Requirement |
|---|---|---|
| FR-STS-001 | Must | Sistem harus mendukung status `Draft`, `Waiting Acceptance`, `Accepted`, `On The Way`, `Arrived`, `Installation`, `Finished`, `Rejected`, `Cancelled`, dan `Failed`. |
| FR-STS-002 | Must | Backend harus memvalidasi transisi status berdasarkan state machine yang disepakati. |
| FR-STS-003 | Must | Frontend web dan mobile hanya boleh menampilkan aksi yang valid untuk status aktif dan peran pengguna. |
| FR-STS-004 | Must | Setiap perubahan status harus membuat riwayat yang memuat status lama, status baru, waktu, pelaku, dan alasan bila relevan. |
| FR-STS-005 | Must | Status `Cancelled`, `Rejected`, dan `Failed` harus menyimpan alasan. |
| FR-STS-006 | Must | Teknisi harus dapat memulai perjalanan hanya dari status `Accepted`. |
| FR-STS-007 | Must | Teknisi harus dapat menandai tiba hanya dari status `On The Way`. |
| FR-STS-008 | Must | Teknisi harus dapat memulai pemasangan dari status `Arrived` dan menyelesaikan pekerjaan dari status `Installation`. |

## 7. Mobile Teknisi dan Live Tracking

| ID | Prioritas | Requirement |
|---|---|---|
| FR-TRK-001 | Must | Aplikasi mobile harus menampilkan daftar assignment aktif milik teknisi beserta customer, alamat, jadwal, produk/layanan, dan catatan. |
| FR-TRK-002 | Must | Saat teknisi memulai perjalanan, aplikasi harus meminta/memastikan izin lokasi yang diperlukan dan mengaktifkan pengiriman lokasi. |
| FR-TRK-003 | Must | Sistem harus mengaktifkan Tracking Session saat status berubah menjadi `On The Way`. |
| FR-TRK-004 | Must | Aplikasi harus mengirim latitude, longitude, akurasi, dan waktu perangkat ke API tracking hanya untuk Tracking Session aktif. |
| FR-TRK-005 | Must | Backend harus menolak lokasi dari teknisi yang bukan pemilik assignment atau sesi yang tidak aktif. |
| FR-TRK-006 | Must | Sistem harus menyimpan lokasi terkini untuk kebutuhan realtime dan menyimpan histori lokasi secara terkontrol. |
| FR-TRK-007 | Must | Aplikasi harus menghentikan pengiriman lokasi saat status menjadi `Arrived`, `Finished`, `Cancelled`, atau `Failed`. |
| FR-TRK-008 | Must | Jika jaringan sementara tidak tersedia, aplikasi harus mengantre data lokasi yang belum terkirim dan mengirim ulang saat koneksi kembali tersedia. |
| FR-TRK-009 | Should | Aplikasi harus memberi indikator kepada teknisi ketika izin lokasi, koneksi, atau tracking background tidak aktif. |

## 8. Customer Tracking Portal

| ID | Prioritas | Requirement |
|---|---|---|
| FR-CTP-001 | Must | Sistem harus menghasilkan tautan tracking unik untuk Work Order/Tracking Session. |
| FR-CTP-002 | Must | Tautan tracking hanya aktif ketika Work Order berstatus `On The Way` dan Tracking Session aktif. |
| FR-CTP-003 | Must | Customer harus dapat melihat status pekerjaan, lokasi teknisi terkini, dan lokasi tujuan melalui tautan aktif tanpa login. |
| FR-CTP-004 | Must | Customer portal harus menerima pembaruan lokasi/status secara realtime melalui WebSocket atau mekanisme realtime yang setara. |
| FR-CTP-005 | Must | Sistem harus memvalidasi token, masa berlaku, dan status sesi sebelum menyajikan data tracking. |
| FR-CTP-006 | Must | Customer portal tidak boleh memperlihatkan nomor telepon teknisi, histori lokasi lengkap, atau data Work Order lain. |
| FR-CTP-007 | Must | Sistem harus menonaktifkan atau membuat token tidak berlaku saat Work Order selesai, dibatalkan, gagal, atau melewati masa berlaku. |

## 9. Monitoring Koordinator

| ID | Prioritas | Requirement |
|---|---|---|
| FR-MON-001 | Must | Koordinator harus dapat melihat daftar Work Order berdasarkan status dan tanggal. |
| FR-MON-002 | Must | Koordinator harus dapat melihat detail Work Order, assignment, histori status, dan alasan perubahan. |
| FR-MON-003 | Must | Koordinator harus dapat melihat posisi terkini teknisi yang sedang menjalankan Work Order berstatus `On The Way`. |
| FR-MON-004 | Must | Dashboard monitoring harus menerima pembaruan status dan lokasi secara realtime. |
| FR-MON-005 | Should | Koordinator harus dapat melihat histori perjalanan yang dipersistenkan untuk Work Order yang selesai. |

## 10. Notifikasi

| ID | Prioritas | Requirement |
|---|---|---|
| FR-NTF-001 | Must | Sistem harus mengirim notifikasi assignment baru kepada teknisi melalui push notification. |
| FR-NTF-002 | Must | Sistem harus mengirim notifikasi kepada koordinator untuk acceptance, rejection, dan perubahan status penting. |
| FR-NTF-003 | Must | Sistem harus mengirim konfirmasi jadwal kepada customer setelah assignment diterima. |
| FR-NTF-004 | Must | Sistem harus mengirim tautan tracking kepada customer ketika teknisi memulai perjalanan. |
| FR-NTF-005 | Must | Pengiriman push, WhatsApp, dan email harus diproses melalui queue agar aksi utama tidak menunggu provider eksternal. |
| FR-NTF-006 | Must | Sistem harus mencatat status pengiriman notifikasi untuk keperluan troubleshooting dan audit. |
| FR-NTF-007 | Should | Sistem harus mendukung WhatsApp dan email sebagai channel yang dapat dikonfigurasi. |

## 11. Master Data dan Audit

| ID | Prioritas | Requirement |
|---|---|---|
| FR-MST-001 | Must | Administrator harus dapat mengelola data teknisi dan status aktifnya. |
| FR-MST-002 | Should | Administrator harus dapat mengelola area kerja dan kendaraan untuk pengembangan dispatch berikutnya. |
| FR-AUD-001 | Must | Sistem harus merekam histori status Work Order dan histori Assignment. |
| FR-AUD-002 | Must | Sistem harus menyimpan alasan untuk penolakan, pembatalan, dan kegagalan pekerjaan. |
| FR-AUD-003 | Must | Sistem harus memungkinkan pengguna berwenang menelusuri Work Order berdasarkan invoice/nomor Work Order. |

## 12. Acceptance Criteria MVP

MVP diterima secara fungsional apabila koordinator dapat membuat Work Order dari transaksi penjualan, mengirim assignment kepada teknisi, teknisi dapat menerima dan memulai perjalanan, customer dapat melihat tracking secara realtime melalui tautan terbatas, serta Work Order dapat berakhir dengan histori status dan tracking yang dapat ditelusuri.

