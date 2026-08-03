<?php

return [
    /*
    | Password bawaan akun teknisi yang dibuat dari data legacy.
    | Dipakai agar teknisi bisa login pertama kali, lalu segera
    | menggantinya sendiri lewat menu Ganti Password di aplikasi.
    */
    'technician_default_password' => env('FSM_TECH_DEFAULT_PASSWORD', '12345'),
];
