<?php

return [
    'paypal' => [
        'enabled' => env('PAYPAL_ENABLED', false),
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'sandbox' => env('PAYPAL_SANDBOX', true),
        'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
    ],
    
    'fastlane' => [
        'enabled' => env('FASTLANE_ENABLED', false),
        'api_key' => env('FASTLANE_API_KEY'),
        'sandbox' => env('FASTLANE_SANDBOX', true),
        'webhook_secret' => env('FASTLANE_WEBHOOK_SECRET'),
    ],
    
    'bank_transfer' => [
        'enabled' => env('BANK_TRANSFER_ENABLED', true),
        'details' => env('BANK_TRANSFER_DETAILS', 'اسم البنك: البنك الأهلي السعودي\nرقم الحساب: 1234567890\nاسم المستفيد: حصوني للتعليم'),
    ],
    
    'cash' => [
        'enabled' => env('CASH_ENABLED', true),
        'instructions' => env('CASH_INSTRUCTIONS', 'يمكن الدفع نقداً في مقر المعهد\nأو عبر المدرس المسؤول'),
    ],
];