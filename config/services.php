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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URI'),
    ],

    'telegram' => [
        'bot_name' => env('TELEGRAM_BOT_NAME'),
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'redirect' => env('TELEGRAM_REDIRECT_URI'),
    ],

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
        'verify_url' => env('RECAPTCHA_VERIFY_URL', 'https://www.google.com/recaptcha/api/siteverify'),
    ],

    'google_cloud_tts' => [
        'service_account_json' => env('GOOGLE_CLOUD_TTS_SERVICE_ACCOUNT_JSON'),
        'service_account_json_path' => env('GOOGLE_CLOUD_TTS_SERVICE_ACCOUNT_JSON_PATH'),
    ],

    'ga4' => [
        'property_id' => env('GA4_PROPERTY_ID'),
        'service_account_json_path' => env('GA4_SERVICE_ACCOUNT_JSON_PATH', 'storage/app/google/ga4-service-account.json'),
    ],

    'elevenlabs' => [
        'api_key' => env('ELEVENLABS_API_KEY'),
        'model' => env('ELEVENLABS_TTS_MODEL', 'eleven_multilingual_v2'),
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

    // Store Bakong Open API credentials for checkout verification requests.
    'bakong_open_api' => [
        'base_url' => env('BAKONG_OPEN_API_BASE_URL', 'https://api-bakong.nbc.gov.kh'),
        'token' => env('BAKONG_OPEN_API_TOKEN'),
    ],

    // Store Bakong KHQR merchant info for generating live payment QR.
    'bakong_khqr' => [
        'mode' => env('BAKONG_KHQR_MODE', 'generated'),
        'account_id' => env('BAKONG_KHQR_ACCOUNT_ID'),
        'merchant_name' => env('BAKONG_KHQR_MERCHANT_NAME', 'TechCourse'),
        'merchant_city' => env('BAKONG_KHQR_MERCHANT_CITY', 'Phnom Penh'),
        'mobile_number' => env('BAKONG_KHQR_MOBILE_NUMBER'),
        'static_image_url' => env('BAKONG_KHQR_STATIC_IMAGE_URL'),
        'static_qr_string' => env('BAKONG_KHQR_STATIC_QR_STRING'),
        'app_name' => env('BAKONG_KHQR_APP_NAME', 'TechCourse'),
        'app_icon_url' => env('BAKONG_KHQR_APP_ICON_URL'),
        'callback_url' => env('BAKONG_KHQR_CALLBACK_URL', env('APP_URL')),
        'token' => env('BAKONG_KHQR_TOKEN', env('BAKONG_OPEN_API_TOKEN')),
    ],

];
