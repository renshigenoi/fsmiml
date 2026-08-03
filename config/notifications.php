<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Notification Channels
    |--------------------------------------------------------------------------
    |
    | Each channel maps to a driver. `log` is a safe local-development driver
    | that writes the composed message to the application log without calling
    | an external provider. Switch to `fcm`, `fonnte`, `wablas`, `meta`, or
    | the Laravel mail driver once real credentials are available.
    |
    */

    'channels' => [
        'push' => [
            'driver' => env('FCM_DRIVER', 'log'),
        ],

        'whatsapp' => [
            'driver' => env('WHATSAPP_DRIVER', 'log'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Cloud Messaging (FCM)
    |--------------------------------------------------------------------------
    |
    | FCM_CREDENTIALS must point to the Google service account JSON file.
    | FCM_DRY_RUN sends messages without delivering them to devices.
    |
    */

    'fcm' => [
        'project_id' => env('FCM_PROJECT_ID'),
        'credentials' => env('FCM_CREDENTIALS'),
        'dry_run' => (bool) env('FCM_DRY_RUN', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Providers
    |--------------------------------------------------------------------------
    |
    | Supported drivers:
    |   fonnte -> https://api.fonnte.com/send
    |   wablas -> https://{domain}/api/send-message
    |   meta   -> WhatsApp Business Cloud API
    |
    */

    'whatsapp' => [
        'default_country_code' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '62'),

        'fonnte' => [
            'token' => env('FONNTE_TOKEN'),
        ],
        'wablas' => [
            'token' => env('WABLAS_TOKEN'),
            'domain' => env('WABLAS_DOMAIN'),
        ],
        'meta' => [
            'token' => env('META_WHATSAPP_TOKEN'),
            'phone_number_id' => env('META_WHATSAPP_PHONE_NUMBER_ID'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Customer Tracking Link
    |--------------------------------------------------------------------------
    | Base URL used to build the public customer tracking link. Point this to
    | the customer-facing frontend in production (e.g. https://tracking.fsm.com).
    */

    'tracking' => [
        'public_url' => rtrim((string) env('TRACKING_PUBLIC_URL', env('APP_URL', 'http://localhost')), '/'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Delivery Queue
    |--------------------------------------------------------------------------
    */

    'queue' => env('NOTIFICATION_QUEUE', 'notifications'),
    'retry' => [
        'tries' => (int) env('NOTIFICATION_TRIES', 3),
        'backoff' => [10, 60, 300],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sender
    |--------------------------------------------------------------------------
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'FSM')),
    ],
];
