<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'aba_payway' => [
        'merchant_id' => env('ABA_PAYWAY_MERCHANT_ID'),
        'api_key' => env('ABA_PAYWAY_API_KEY'),
        'rsa_public_key' => env('ABA_PAYWAY_RSA_PUBLIC_KEY'),
        'rsa_private_key' => env('ABA_PAYWAY_RSA_PRIVATE_KEY'),
        'purchase_url' => env('ABA_PAYWAY_PURCHASE_URL'),
        'generate_qr_url' => env('ABA_PAYWAY_GENERATE_QR_URL'),
        'check_transaction_url' => env('ABA_PAYWAY_CHECK_TRANSACTION_URL'),
        'currency' => env('ABA_PAYWAY_CURRENCY', 'USD'),
        'payment_option' => env('ABA_PAYWAY_PAYMENT_OPTION', 'abapay_deeplink'),
        'return_url' => env('ABA_PAYWAY_RETURN_URL'),
        'cancel_url' => env('ABA_PAYWAY_CANCEL_URL'),
        'callback_url' => env('ABA_PAYWAY_CALLBACK_URL'),
    ],

];
