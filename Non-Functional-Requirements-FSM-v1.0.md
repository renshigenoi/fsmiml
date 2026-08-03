# Non-Functional Requirements (NFR)

## Field Service Management (FSM) v1.0 — Live Tracking & Job Dispatch

**Versi:** 1.0 (draft)  
**Dasar:** Functional Requirements FSM v1.0  
**Catatan:** Target di bawah merupakan baseline MVP dan harus divalidasi kembali pada capacity planning sebelum production.

---

## 1. Performa dan Realtime

| ID | Target / Requirement |
|---|---|
| NFR-PERF-001 | API non-tracking harus memiliki waktu respons p95 ≤ 1 detik pada beban operasional normal, tidak termasuk waktu layanan pihak ketiga. |
| NFR-PERF-002 | Pembaruan lokasi dari API yang valid harus tersedia di customer portal dan dashboard koordinator dengan target end-to-end p95 ≤ 3 detik pada koneksi normal. |
| NFR-PERF-003 | Aksi perubahan status harus tersimpan secara atomik dan mengembalikan respons kepada aplikasi tanpa menunggu proses notifikasi eksternal. |
| NFR-PERF-004 | Pengiriman notifikasi, persistensi histori GPS, dan broadcast yang tidak kritis harus dijalankan melalui queue. |
| NFR-PERF-005 | Lokasi terkini harus menggunakan Redis/cache; PostgreSQL tidak digunakan untuk polling lokasi realtime setiap beberapa detik. |
| NFR-PERF-006 | Persistensi Tracking Point harus dikendalikan konfigurasi interval dan/atau perubahan jarak; baseline awal paling banyak satu titik per 30 detik per sesi. |

## 2. Kapasitas dan Skalabilitas

| ID | Target / Requirement |
|---|---|
| NFR-SCL-001 | MVP harus dirancang untuk sedikitnya 100 teknisi terdaftar dan 50 teknisi aktif tracking secara bersamaan. |
| NFR-SCL-002 | Arsitektur harus memungkinkan peningkatan kapasitas worker queue, Reverb, dan Redis secara independen tanpa mengubah kontrak API. |
| NFR-SCL-003 | Modul Tracking harus bergantung pada `work_order_id`, bukan pada jenis pemasangan tertentu. |
| NFR-SCL-004 | Desain database harus menggunakan indeks pada kolom pencarian dan relasi utama, khususnya status, jadwal, technician, work order, dan waktu tracking. |
| NFR-SCL-005 | Jika volume tracking melebihi kapasitas modular monolith, Tracking dapat dipisahkan menjadi service tersendiri tanpa mengubah konsep domain atau pengalaman pengguna. |

## 3. Ketersediaan dan Keandalan

| ID | Target / Requirement |
|---|---|
| NFR-AVL-001 | Target ketersediaan layanan production MVP adalah ≥ 99,5% per bulan, tidak termasuk maintenance terjadwal yang diumumkan. |
| NFR-AVL-002 | Kegagalan provider notifikasi tidak boleh membatalkan penyimpanan Work Order, Assignment, status, atau Tracking Point. |
| NFR-AVL-003 | Job queue yang gagal harus dapat dicoba ulang dengan kebijakan retry dan memiliki dead-letter/failed-job record untuk investigasi. |
| NFR-AVL-004 | Jika koneksi mobile terputus, aplikasi harus menyimpan antrean lokasi/status secara lokal dan melakukan retry ketika koneksi pulih. |
| NFR-AVL-005 | Sistem harus menangani reconnect WebSocket tanpa mengharuskan customer atau koordinator memuat ulang halaman secara manual. |
| NFR-AVL-006 | Operasi yang mengubah state harus idempotent atau memiliki proteksi duplikasi untuk mencegah efek ganda akibat retry jaringan. |

## 4. Keamanan dan Privasi

| ID | Target / Requirement |
|---|---|
| NFR-SEC-001 | Seluruh trafik production harus menggunakan HTTPS/TLS yang valid. |
| NFR-SEC-002 | Pengguna internal harus diautentikasi melalui Laravel Sanctum; token, sesi, dan kredensial tidak boleh dicatat dalam log aplikasi. |
| NFR-SEC-003 | Otorisasi berbasis peran dan kepemilikan assignment harus selalu diverifikasi di backend. |
| NFR-SEC-004 | Tracking token customer harus acak, sulit ditebak, terbatas pada satu sesi, dan memiliki masa berlaku. |
| NFR-SEC-005 | Customer portal hanya boleh mendapatkan data minimum untuk tracking; data pribadi atau histori lokasi tidak boleh diekspos tanpa kebutuhan bisnis yang disetujui. |
| NFR-SEC-006 | Input API harus divalidasi dan disanitasi; API harus menerapkan rate limit, terutama pada endpoint autentikasi, public tracking, dan GPS update. |
| NFR-SEC-007 | Secret seperti database password, API key FCM, WhatsApp token, dan SMTP credential harus disimpan di secret manager atau environment configuration, tidak pernah di source control. |
| NFR-SEC-008 | Aksi administratif dan perubahan status harus memiliki audit trail yang memuat pelaku dan waktu. |

## 5. Integritas Data dan Retensi

| ID | Target / Requirement |
|---|---|
| NFR-DATA-001 | Perubahan Work Order, Assignment, Tracking Session, dan histori status yang saling bergantung harus dilakukan dalam transaksi database. |
| NFR-DATA-002 | Foreign key, unique constraint, dan validasi domain harus mencegah referensi data yang tidak sah dan duplikasi transaksi yang tidak diinginkan. |
| NFR-DATA-003 | Waktu sistem harus disimpan konsisten dalam UTC dan ditampilkan dalam zona waktu operasional yang dikonfigurasi (awal: Asia/Jakarta). |
| NFR-DATA-004 | Lokasi GPS hanya boleh disimpan selama periode retensi yang disetujui bisnis; nilai awal harus dikonfigurasi sebelum production. |
| NFR-DATA-005 | Data histori dan audit tidak boleh diubah secara langsung oleh pengguna aplikasi biasa. |
| NFR-DATA-006 | Penyimpanan cache lokasi harus menggunakan TTL sehingga lokasi lama tidak terus tampil sebagai lokasi aktif. |

## 6. Backup dan Pemulihan

| ID | Target / Requirement |
|---|---|
| NFR-BKP-001 | Database PostgreSQL production harus memiliki backup otomatis harian dan mekanisme retensi backup yang terdokumentasi. |
| NFR-BKP-002 | Backup harus diuji pemulihannya secara berkala sebelum sistem dinyatakan siap production. |
| NFR-BKP-003 | Target awal RPO adalah maksimal 24 jam dan target RTO maksimal 8 jam; target ini dapat diperketat sesuai kebutuhan bisnis. |
| NFR-BKP-004 | Konfigurasi infrastruktur, secret recovery procedure, dan prosedur restore harus terdokumentasi dan aksesnya dibatasi. |

## 7. Observability dan Operasional

| ID | Target / Requirement |
|---|---|
| NFR-OBS-001 | Aplikasi harus menghasilkan log terstruktur untuk error, autentikasi gagal, perubahan status, dan kegagalan integrasi. |
| NFR-OBS-002 | Log tidak boleh memuat password, token autentikasi, isi credential, atau lokasi GPS lebih banyak dari yang diperlukan untuk investigasi. |
| NFR-OBS-003 | Sistem harus memantau kesehatan API, database, Redis, queue worker, dan WebSocket service. |
| NFR-OBS-004 | Failed job, lonjakan error API, dan service yang tidak sehat harus menghasilkan alert kepada operator yang ditetapkan. |
| NFR-OBS-005 | Setiap request harus memiliki correlation/request ID agar alur error dapat ditelusuri lintas API, queue, dan notifikasi. |

## 8. Usability dan Kompatibilitas

| ID | Target / Requirement |
|---|---|
| NFR-USE-001 | Antarmuka teknisi harus dioptimalkan untuk perangkat mobile Android dan penggunaan satu tangan di lapangan. |
| NFR-USE-002 | Status pekerjaan, tindakan berikutnya, dan status tracking harus mudah dipahami tanpa pelatihan teknis khusus. |
| NFR-USE-003 | Customer tracking page harus responsif pada browser mobile modern tanpa memerlukan aplikasi atau login. |
| NFR-CMP-001 | Web admin dan customer portal harus mendukung dua versi terakhir browser Chrome, Edge, Firefox, dan Safari yang tersedia pada saat rilis. |
| NFR-CMP-002 | Aplikasi mobile MVP menargetkan Android; dukungan iOS dievaluasi sebagai scope fase berikutnya. |

## 9. Maintainability dan Delivery

| ID | Target / Requirement |
|---|---|
| NFR-MNT-001 | Backend harus mengikuti struktur modular berdasarkan domain (Work Order, Assignment, Tracking, Notification, dan lain-lain). |
| NFR-MNT-002 | Kontrak API harus diberi versi, dimulai dari `/api/v1`, dan didokumentasikan sebelum implementasi frontend/mobile bergantung padanya. |
| NFR-MNT-003 | Perubahan state machine dan aturan domain harus dipusatkan pada service/domain layer, bukan ditanam tersebar di controller. |
| NFR-MNT-004 | Proses deployment harus dapat diulang dan terdokumentasi; konfigurasi environment dipisahkan dari kode aplikasi. |
| NFR-MNT-005 | Fitur kritis—autentikasi, state transition, assignment, dan tracking authorization—harus memiliki automated test. |

## 10. Keputusan yang Diperlukan Sebelum Production

1. Retensi resmi Tracking Point dan audit log.
2. Kebijakan RPO/RTO final serta media/penyimpanan backup.
3. Provider WhatsApp dan email yang akan digunakan.
4. Platform hosting dan batas kapasitas awal.
5. SLA operasional untuk respons assignment dan penanganan failed job.

