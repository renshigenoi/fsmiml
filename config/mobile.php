<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mobile App Version & Update
    |--------------------------------------------------------------------------
    | Dipakai APK Capacitor untuk cek pembaruan. Ada DUA jenis versi:
    |
    |  1. NATIVE  -> isi APK (plugin, permission, folder android/). Perubahan
    |               di sini WAJIB build + install ulang APK. Dikontrol oleh
    |               'native_version' + 'download_url'.
    |
    |  2. BUNDLE  -> web bundle (folder mobile/src -> dist: Vue/CSS/JS/desain).
    |               Bisa di-push tanpa install ulang (live update / OTA).
    |               Dikontrol oleh 'bundle_version' + 'bundle_url'.
    |
    | Cukup ubah nilai di .env saat rilis — tidak perlu sentuh kode.
    */

    // --- NATIVE (wajib install ulang APK) ---
    'native_version' => env('MOBILE_APP_VERSION', '1.0.0'),
    'download_url' => env('MOBILE_APP_DOWNLOAD_URL'),

    // Jika native user < min ini, update dipaksa (tidak bisa dilewati).
    'min_native_version' => env('MOBILE_APP_MIN_VERSION', '1.0.0'),
    'update_required' => (bool) env('MOBILE_APP_UPDATE_REQUIRED', false),

    // --- BUNDLE (live update, tanpa install ulang) ---
    // Naikkan angka ini setiap kali build ulang web bundle (desain/logika UI).
    'bundle_version' => (int) env('MOBILE_BUNDLE_VERSION', 1),
    'bundle_url' => env('MOBILE_BUNDLE_URL'),
];
