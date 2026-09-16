<?php

return [
    /*
    | Password bawaan akun teknisi yang dibuat dari data legacy.
    | Dipakai agar teknisi bisa login pertama kali, lalu segera
    | menggantinya sendiri lewat menu Ganti Password di aplikasi.
    */
    'technician_default_password' => env('FSM_TECH_DEFAULT_PASSWORD', '12345'),

    /*
    | Jendela penjadwalan default (jam) yang dipakai cek benturan jadwal teknisi
    | bila Work Order tidak punya scheduled_end_at (WO dari legacy tidak pernah
    | mengisi kolom tersebut).
    */
    'schedule_default_duration_hours' => (float) env('FSM_SCHEDULE_DURATION_HOURS', 3),
];
