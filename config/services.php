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

    'netease' => [
        'base_url' => env('NETEASE_API_URL', 'https://pay.neteasegames.com/gameclub'),
        'timeout' => (int) env('NETEASE_API_TIMEOUT', 20),
        // TTL cache danh sach san pham (phut) - NetEase doi gia/pack theo dot.
        // deviceid trang topup tu sinh 1 lan roi giu trong localStorage.
        'device_id' => env('NETEASE_DEVICE_ID', '208134903320578761'),
        'products_ttl' => (int) env('NETEASE_PRODUCTS_TTL', 360),
        'user_agent' => env('NETEASE_USER_AGENT', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36'),
    ],

    'doitac' => [
        'url' => env('DOITAC_API_URL', 'https://doitac.top/api/v1/api/orders'),
        'token' => env('DOITAC_API_TOKEN'),
        'timeout' => (int) env('DOITAC_API_TIMEOUT', 20),
        // So lan goi POST update status (ke ca lan dau): doi tac khong tra 200
        // thi goi lai, vi don da vao DB roi ma ben do van la "new".
        'status_attempts' => (int) env('DOITAC_STATUS_ATTEMPTS', 3),
        'status_retry_delay' => (int) env('DOITAC_STATUS_RETRY_DELAY', 2),
    ],

];
