<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Active Payment Gateway
    |--------------------------------------------------------------------------
    | Supported: "toyyibpay", "billplz"
    */
    'gateway' => env('PAYMENT_GATEWAY', 'toyyibpay'),

    /*
    |--------------------------------------------------------------------------
    | ToyyibPay Configuration
    |--------------------------------------------------------------------------
    */
    'toyyibpay' => [
        'secret_key'    => env('TOYYIBPAY_SECRET_KEY'),
        'category_code' => env('TOYYIBPAY_CATEGORY_CODE'),
        'sandbox'       => env('TOYYIBPAY_SANDBOX', false),
        'base_url'      => env('TOYYIBPAY_SANDBOX', false)
                            ? 'https://dev.toyyibpay.com'
                            : 'https://toyyibpay.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | Billplz Configuration (optional)
    |--------------------------------------------------------------------------
    */
    'billplz' => [
        'api_key'       => env('BILLPLZ_API_KEY'),
        'collection_id' => env('BILLPLZ_COLLECTION_ID'),
        'sandbox'       => env('BILLPLZ_SANDBOX', false),
        'base_url'      => env('BILLPLZ_SANDBOX', false)
                            ? 'https://www.billplz-sandbox.com'
                            : 'https://www.billplz.com',
    ],

];
