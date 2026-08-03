# System Flow

## Field Service Management (FSM) v1.0 — Live Tracking & Job Dispatch

**Versi:** 1.0 (draft)  
**Prasyarat:** BRD FSM v1.0  
**Tujuan:** Acuan teknis untuk API, database, queue, event, WebSocket, web admin, dan aplikasi teknisi.

---

## 1. Prinsip Desain

- `Work Order` (WO) adalah entitas utama pekerjaan. Modul Tracking hanya mereferensikan `work_order_id`, sehingga dapat digunakan untuk pemasangan, garansi, servis ulang, atau survey lokasi.
- Laravel berjalan sebagai modular monolith; proses berat seperti notifikasi dan penyimpanan histori dijalankan melalui queue.
- PostgreSQL menyimpan data bisnis dan histori. Redis digunakan untuk data lokasi terkini, cache, queue, dan fan-out realtime.
- Perubahan status bersifat server-authoritative: API memvalidasi peran pengguna dan transisi state sebelum menyimpan data.
- Customer tidak mengirim maupun meminta GPS dari perangkat teknisi. Customer hanya menerima data yang telah divalidasi melalui WebSocket.

## 2. Komponen Sistem

```text
Sales System
    │ (API / read-only integration / import)
    ▼
FSM Backend (Laravel)
    ├── Scheduling & Dispatch
    ├── Work Order
    ├── Tracking
    ├── Notification
    └── Customer Portal
    │                 │
    ▼                 ▼
PostgreSQL          Redis
    │                 ├── cache lokasi terkini
    │                 ├── queue
    │                 └── publish realtime
    ▼                 │
Histori             Laravel Reverb
                      │
          ┌───────────┴───────────┐
          ▼                       ▼
      Vue Admin           Customer Tracking Page

Capacitor Mobile App → GPS perangkat → Tracking API → Redis
```

## 3. Flow 1 — Membuat Assignment

```text
Koordinator → Web Admin → FSM API
                              │
               validasi input, customer, teknisi, jadwal
                              │
                              ▼
                     transaksi PostgreSQL
                   ├─ simpan / perbarui Work Order
                   ├─ simpan Assignment
                   ├─ buat Tracking Session (belum aktif)
                   └─ catat riwayat status
                              │
                              ▼
              status: WAITING_ACCEPTANCE
                              │
                              ▼
                     event AssignmentCreated
                              │
                              ▼
                   queue kirim push notification
```

Ketentuan:

- Validasi bentrok jadwal dilakukan sebelum penyimpanan. Definisi bentrok (berdasarkan rentang jadwal, area, atau kapasitas teknisi) ditetapkan pada desain scheduling.
- Penyimpanan Work Order, Assignment, Tracking Session, dan histori status dilakukan secara atomik dalam satu transaksi database.
- Tracking token boleh dibuat sejak assignment dibuat, namun baru dapat dipakai saat tracking session berstatus aktif.

## 4. Flow 2 — Teknisi Accept atau Reject

```text
Mobile App → POST /assignments/{id}/accept atau /reject
                    │
                    ▼
          autentikasi, otorisasi, dan lock assignment
                    │
                    ▼
             validasi status saat ini
                    │
                    ▼
          transaksi PostgreSQL: update status + histori
                    │
                    ▼
     event AssignmentAccepted / AssignmentRejected
                    │
                    ▼
    queue: notifikasi koordinator dan customer sesuai event
```

Pada `Accept`, customer menerima konfirmasi jadwal. Pada `Reject`, alasan penolakan wajib tersimpan dan koordinator menerima notifikasi untuk reassignment atau reschedule.

## 5. Flow 3 — Start Trip

```text
Teknisi tekan Start Trip
        │
        ▼
Mobile memeriksa izin lokasi dan mengaktifkan GPS background
        │
        ▼
POST /work-orders/{id}/start-trip
        │
        ▼
API memvalidasi state ACCEPTED dan kepemilikan assignment
        │
        ▼
database: status ON_THE_WAY, aktifkan tracking session, simpan histori
        │
        ▼
event TripStarted → queue kirim link tracking customer
```

Apabila izin lokasi ditolak atau GPS tidak dapat diaktifkan, aplikasi tidak boleh melaporkan perjalanan sebagai berhasil. Sistem menampilkan tindakan pemulihan kepada teknisi dan mencatat kegagalan yang relevan.

## 6. Flow 4 — Pembaruan GPS

```text
Android GPS → Capacitor Mobile
                  │
                  ▼
       POST /api/tracking/locations
                  │
                  ▼
  autentikasi + validasi assignment/tracking session aktif
                  │
                  ▼
      Redis: simpan lokasi terkini dengan TTL
                  │
          ┌───────┴─────────┐
          ▼                 ▼
Laravel Reverb          Queue histori
customer/admin          PostgreSQL pada interval/batch
```

Ketentuan:

- Payload lokasi minimal memuat latitude, longitude, akurasi, waktu perangkat, dan indikator sumber lokasi.
- API menolak pembaruan jika work order tidak sedang `ON_THE_WAY` atau teknisi bukan pemilik assignment.
- Broadcast hanya dilakukan kepada channel yang diotorisasi.
- Database tidak harus menerima setiap titik GPS. Worker menyimpan titik secara berkala (awal: paling banyak satu titik setiap 30 detik per sesi), dengan aturan tambahan berbasis jarak dapat ditetapkan kemudian.
- TTL Redis dan interval penyimpanan histori menjadi konfigurasi sistem, bukan nilai yang di-hardcode.

## 7. Flow 5 — Customer Tracking

```text
Customer membuka tautan tracking
        │
        ▼
GET /track/{token}
        │
        ▼
validasi token, expiry, dan tracking session
        │
        ▼
render Customer Tracking Page
        │
        ▼
otorisasi channel WebSocket untuk token tersebut
        │
        ▼
terima lokasi/status realtime dan perbarui marker
```

Halaman customer hanya dapat membaca status, lokasi perjalanan saat diizinkan, dan lokasi tujuan. Tautan tidak memaparkan identitas sensitif teknisi ataupun histori titik lokasi penuh.

## 8. Flow 6 — Arrived dan Installation

```text
Teknisi tekan Arrived → API validasi ON_THE_WAY
        │
        ▼
database: status ARRIVED + histori; nonaktifkan pengumpulan GPS
        │
        ▼
broadcast status ke admin/customer
        │
        ▼
Teknisi tekan Start Installation → status INSTALLATION + histori
```

Status `Arrived` dan `Installation` dipisahkan agar waktu perjalanan dan waktu pemasangan dapat dianalisis pada fase berikutnya.

## 9. Flow 7 — Finish, Cancel, atau Failed

```text
Teknisi/koordinator menjalankan aksi terminal
        │
        ▼
API validasi peran dan transisi state
        │
        ▼
database: FINISHED / CANCELLED / FAILED + histori + alasan bila wajib
        │
        ▼
tutup tracking session, hapus/biarkan TTL cache lokasi berakhir
        │
        ▼
cabut akses token tracking dan broadcast perubahan akhir
```

`Cancelled` dan `Failed` harus mencatat alasan. Reassignment atau reschedule dilakukan dengan proses assignment berikutnya agar audit trail tetap utuh.

## 10. State Machine

```text
DRAFT ── kirim assignment ──► WAITING_ACCEPTANCE
                                  │          │
                             accept       reject
                                  │          ▼
                                  ▼       REJECTED ──► reassign/reschedule
                               ACCEPTED
                                  │
                              start trip
                                  ▼
                              ON_THE_WAY
                                  │
                               arrived
                                  ▼
                               ARRIVED
                                  │
                          start installation
                                  ▼
                            INSTALLATION
                                  │
                                finish
                                  ▼
                              FINISHED

WAITING_ACCEPTANCE / ACCEPTED / ON_THE_WAY / ARRIVED / INSTALLATION
    └── cancel (berdasarkan otorisasi) ──► CANCELLED
ARRIVED / INSTALLATION
    └── report failure ──────────────────► FAILED
```

Status terminal (`FINISHED`, `CANCELLED`, `FAILED`) tidak memiliki transisi normal. Seluruh transisi disentralisasi dalam service/domain layer Laravel, bukan tersebar dalam controller.

## 11. Event dan Background Job Awal

| Event | Dampak asinkron |
|---|---|
| `AssignmentCreated` | Kirim push assignment ke teknisi. |
| `AssignmentAccepted` | Notifikasi koordinator dan konfirmasi customer. |
| `AssignmentRejected` | Notifikasi koordinator. |
| `TripStarted` | Kirim/aktifkan link tracking customer. |
| `TrackingLocationUpdated` | Broadcast lokasi dan antrekan persistensi histori. |
| `WorkOrderStatusChanged` | Broadcast dashboard/customer dan catat audit bila diperlukan. |
| `TrackingSessionClosed` | Cabut akses token dan bersihkan cache sesuai kebijakan. |

## 12. Hasil yang Menjadi Input ERD

System flow ini mengonfirmasi kebutuhan entitas minimal berikut: `work_orders`, `assignments`, `tracking_sessions`, `tracking_locations`, `work_order_status_histories`, `tracking_tokens`, `notifications`, serta master customer/teknisi dan referensi transaksi penjualan.

