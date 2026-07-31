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

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    ],

    'qris_gateway' => env('QRIS_GATEWAY', 'midtrans'),

    'bni_qris' => [
        'base_url' => env('BNI_QRIS_BASE_URL'),
        'token_path' => env('BNI_QRIS_TOKEN_PATH'),
        'create_qris_path' => env('BNI_QRIS_CREATE_PATH'),
        'inquiry_path' => env('BNI_QRIS_INQUIRY_PATH'),
        'client_id' => env('BNI_QRIS_CLIENT_ID'),
        'client_secret' => env('BNI_QRIS_CLIENT_SECRET'),
        'merchant_id' => env('BNI_QRIS_MERCHANT_ID'),
        'terminal_id' => env('BNI_QRIS_TERMINAL_ID'),
        'private_key' => env('BNI_QRIS_PRIVATE_KEY'),
        'public_key' => env('BNI_QRIS_PUBLIC_KEY'),
        'webhook_secret' => env('BNI_QRIS_WEBHOOK_SECRET'),
        'signature_mode' => env('BNI_QRIS_SIGNATURE_MODE', 'none'),
        'timeout' => env('BNI_QRIS_TIMEOUT', 30),
        'is_production' => env('BNI_QRIS_IS_PRODUCTION', false),
    ],

];
