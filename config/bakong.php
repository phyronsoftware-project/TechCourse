<?php

return [
    'open_api_base_url' => env('BAKONG_OPEN_API_BASE_URL', 'https://api-bakong.nbc.gov.kh'),
    'open_api_token' => env('BAKONG_OPEN_API_TOKEN'),
    'mode' => env('BAKONG_KHQR_MODE', 'generated'),
    'khqr_account_id' => env('BAKONG_KHQR_ACCOUNT_ID'),
    'merchant_name' => env('BAKONG_KHQR_MERCHANT_NAME'),
    'merchant_city' => env('BAKONG_KHQR_MERCHANT_CITY', 'Phnom Penh'),
    'mobile_number' => env('BAKONG_KHQR_MOBILE_NUMBER'),
    'app_name' => env('BAKONG_KHQR_APP_NAME', 'TechCourse'),
    'app_icon_url' => env('BAKONG_KHQR_APP_ICON_URL'),
    'callback_url' => env('BAKONG_KHQR_CALLBACK_URL'),
    'khqr_token' => env('BAKONG_KHQR_TOKEN', env('BAKONG_OPEN_API_TOKEN')),
    'static_image_url' => env('BAKONG_KHQR_STATIC_IMAGE_URL'),
    'static_qr_string' => env('BAKONG_KHQR_STATIC_QR_STRING'),
    'dynamic_expire_minutes' => (int) env('BAKONG_KHQR_DYNAMIC_EXPIRE_MINUTES', 10),
];
