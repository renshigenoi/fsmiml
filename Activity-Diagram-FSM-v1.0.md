# Activity Diagram

## Field Service Management (FSM) v1.0 — Live Tracking & Job Dispatch

**Versi:** 1.0 (draft)  
**Dasar:** Use Case, Functional Requirements, dan System Flow FSM v1.0

---

## 1. Alur Utama: Assignment sampai Selesai

```mermaid
flowchart TD
    A([Mulai]) --> B[Koordinator memilih Sales Order]
    B --> C[Isi jadwal, lokasi, teknisi, dan catatan]
    C --> D{Data valid dan teknisi tersedia?}
    D -- Tidak --> E[Informasikan kesalahan atau konflik jadwal]
    E --> C
    D -- Ya --> F[Buat Work Order dan Assignment]
    F --> G[Status: Waiting Acceptance]
    G --> H[Antrekan push notification teknisi]
    H --> I[Teknisi membuka assignment]
    I --> J{Teknisi menerima?}
    J -- Tidak --> K[Isi alasan penolakan]
    K --> L[Status: Rejected dan beri tahu koordinator]
    L --> M{Koordinator reassign atau reschedule?}
    M -- Ya --> C
    M -- Tidak --> N[Batalkan Work Order]
    N --> Z([Selesai])
    J -- Ya --> O[Status: Accepted]
    O --> P[Kirim konfirmasi jadwal ke customer]
    P --> Q[Teknisi tekan Start Trip]
    Q --> R{Izin GPS tersedia?}
    R -- Tidak --> S[Tampilkan tindakan pemulihan]
    S --> Q
    R -- Ya --> T[Status: On The Way dan aktifkan Tracking Session]
    T --> U[Kirim link tracking ke customer]
    U --> V[Kirim lokasi GPS realtime]
    V --> W[Teknisi tekan Arrived]
    W --> X[Status: Arrived dan hentikan GPS aktif]
    X --> Y[Teknisi mulai pemasangan]
    Y --> AA[Status: Installation]
    AA --> AB{Pekerjaan berhasil?}
    AB -- Ya --> AC[Status: Finished; tutup Tracking Session dan token]
    AC --> Z
    AB -- Tidak --> AD[Status: Failed; simpan alasan dan tutup Tracking Session]
    AD --> Z
```

## 2. Activity: Validasi dan Pengiriman Assignment

```mermaid
flowchart TD
    A([Koordinator klik Kirim Assignment]) --> B[Validasi field Work Order]
    B --> C{Customer dan lokasi tersedia?}
    C -- Tidak --> D[Kembalikan validasi error]
    D --> Z([Selesai])
    C -- Ya --> E{Teknisi aktif?}
    E -- Tidak --> F[Tolak assignment dan tampilkan alasan]
    F --> Z
    E -- Ya --> G{Jadwal bentrok?}
    G -- Ya --> H[Tampilkan konflik jadwal]
    H --> Z
    G -- Tidak --> I[Mulai transaksi database]
    I --> J[Simpan Work Order / Assignment / Tracking Session nonaktif]
    J --> K[Simpan Work Order Status History]
    K --> L[Commit transaksi]
    L --> M[Publish AssignmentCreated]
    M --> N[Worker mengirim push notification]
    N --> Z
```

## 3. Activity: GPS Update dan Realtime Tracking

```mermaid
flowchart TD
    A([Aplikasi memperoleh lokasi GPS]) --> B{Tracking Session aktif dan Work Order On The Way?}
    B -- Tidak --> C[Hentikan / abaikan pengiriman lokasi]
    C --> Z([Selesai])
    B -- Ya --> D{Koneksi tersedia?}
    D -- Tidak --> E[Simpan lokasi pada antrean lokal]
    E --> F[Tunggu koneksi pulih]
    F --> D
    D -- Ya --> G[Kirim lokasi ke Tracking API]
    G --> H{Token, teknisi, dan payload valid?}
    H -- Tidak --> I[Catat penolakan dan beri respons ke aplikasi]
    I --> Z
    H -- Ya --> J[Simpan lokasi terkini di Redis dengan TTL]
    J --> K[Broadcast lokasi ke channel customer dan dashboard]
    K --> L{Waktunya persistensi histori?}
    L -- Ya --> M[Antrekan Tracking Point ke PostgreSQL]
    L -- Tidak --> Z
    M --> Z
```

## 4. Aturan Keputusan Utama

| Keputusan | Hasil jika tidak terpenuhi |
|---|---|
| Data Work Order lengkap | Assignment tidak dibuat; koordinator memperbaiki data. |
| Teknisi aktif dan tidak bentrok | Koordinator memilih teknisi/jadwal lain. |
| Teknisi menerima assignment | Koordinator melakukan reassignment, reschedule, atau pembatalan. |
| Izin GPS tersedia | Perjalanan tidak boleh ditandai aktif sampai kondisi dipulihkan. |
| Sesi tracking valid | Lokasi tidak diterima/ditayangkan. |
| Pekerjaan berhasil | Work Order menjadi `Finished`; bila tidak, menjadi `Failed` dengan alasan. |

## 5. Batasan dan Tindak Lanjut

- Pembatalan dapat terjadi dari status non-terminal sesuai otorisasi; proses ini akan dirinci pada Sequence Diagram dan state transition service.
- Strategi retry GPS, batas antrean offline, serta definisi konflik jadwal akan ditetapkan pada API Design dan desain teknis.
- Diagram berikutnya yang diperlukan adalah Sequence Diagram agar interaksi Vue, mobile, Laravel, Redis, PostgreSQL, queue, Reverb, dan provider notifikasi terlihat berurutan.

