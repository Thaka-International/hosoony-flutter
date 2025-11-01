<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notification Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for various notification channels including FCM, SMTP,
    | WhatsApp, and SMS providers.
    |
    */

    'fcm' => [
        'server_key' => env('FCM_SERVER_KEY'),
        'sender_id' => env('FCM_SENDER_ID'),
    ],

    'smtp' => [
        'host' => env('NOTIFICATION_SMTP_HOST', 'smtp.gmail.com'),
        'port' => env('NOTIFICATION_SMTP_PORT', 587),
        'username' => env('NOTIFICATION_SMTP_USERNAME'),
        'password' => env('NOTIFICATION_SMTP_PASSWORD'),
        'encryption' => env('NOTIFICATION_SMTP_ENCRYPTION', 'tls'),
        'from' => [
            'address' => env('NOTIFICATION_SMTP_FROM_ADDRESS', 'noreply@hosoony.com'),
            'name' => env('NOTIFICATION_SMTP_FROM_NAME', 'نظام حصوني'),
        ],
    ],

    'whatsapp' => [
        'api_url' => env('WHATSAPP_API_URL', 'https://graph.facebook.com/v18.0'),
        'access_token' => env('WHATSAPP_ACCESS_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),
    ],

    'sms' => [
        'provider' => env('SMS_PROVIDER', 'twilio'),
        'twilio' => [
            'sid' => env('SMS_TWILIO_SID'),
            'token' => env('SMS_TWILIO_TOKEN'),
            'from' => env('SMS_TWILIO_FROM'),
        ],
    ],

    'payment' => [
        'gateway' => env('PAYMENT_GATEWAY', 'moyasar'),
        'moyasar' => [
            'secret_key' => env('MOYASAR_SECRET_KEY'),
            'publishable_key' => env('MOYASAR_PUBLISHABLE_KEY'),
        ],
    ],
];


