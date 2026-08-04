<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mobile App Version & Update
    |--------------------------------------------------------------------------
    | Digunakan aplikasi mobile (APK Capacitor) untuk mengecek versi terbaru.
    | Ubah MOBILE_APP_VERSION + MOBILE_APP_DOWNLOAD_URL di .env ketika rilis
    | versi baru — tanpa perlu mengubah kode.
    */

    'version' => env('MOBILE_APP_VERSION', '1.0.0'),
    'download_url' => env('MOBILE_APP_DOWNLOAD_URL'),
    'update_required' => (bool) env('MOBILE_APP_UPDATE_REQUIRED', false),
];
