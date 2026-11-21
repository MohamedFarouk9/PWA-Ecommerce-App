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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-Gateway Payment Providers (Stripe, Paymob, MyFatoorah)
    |--------------------------------------------------------------------------
    */
    'stripe' => [
        'key' => env('STRIPE_PUBLIC_KEY'),
        'secret' => env('STRIPE_SECRET_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'paymob' => [
        'base_url' => env('PAYMOB_BASE_URL', 'https://accept.paymob.com'),
        'secret_key' => env('PAYMOB_SECRET_KEY'),
        'public_key' => env('PAYMOB_PUBLIC_KEY'),
        'integration_id' => (int) env('PAYMOB_INTEGRATION_ID', 0),
        'hmac_secret' => env('PAYMOB_HMAC_SECRET'),
    ],

    'myfatoorah' => [
        'base_url' => env('MYFATOORAH_BASE_URL', 'https://apitest.myfatoorah.com'),
        'api_key' => env('MYFATOORAH_API_KEY'),
        'webhook_secret' => env('MYFATOORAH_WEBHOOK_SECRET'),
    ],

];
