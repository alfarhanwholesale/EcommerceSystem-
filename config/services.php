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

    'easyparcel' => [
        'client_id' => env('EASYPARCEL_CLIENT_ID'),
        'client_secret' => env('EASYPARCEL_CLIENT_SECRET'),
        'url' => env('EASYPARCEL_BASE_URL', 'https://connect.easyparcel.my/?ac='),
        'sandbox' => env('EASYPARCEL_SANDBOX', false),
        'origin_postcode' => env('EASYPARCEL_ORIGIN_POSTCODE', '47100'),
        'origin_city' => env('EASYPARCEL_ORIGIN_CITY', 'Puchong'),
        'origin_state' => env('EASYPARCEL_ORIGIN_STATE', 'Selangor'),
        'origin_name' => env('EASYPARCEL_ORIGIN_NAME', 'Alfarhan Trading'),
        'origin_phone' => env('EASYPARCEL_ORIGIN_PHONE', '0123456789'),
        'origin_address' => env('EASYPARCEL_ORIGIN_ADDRESS', 'No 1, Jalan Puchong, Industri Puchong'),
    ],

];
