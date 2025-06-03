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
    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    ],
    'marketstack' => [
        'api_key' => env('MARKETSTACK_API_KEY'),
        'base_url' => 'http://api.marketstack.com/v1/',
    ],
    'firebase_cfg' => [
        'api_key' => env('API_KEY'),
        'auth_domain' => env('AUTH_DOMAIN'),
        'project_id' => env('PROJECT_ID'),
        'storage_bucket' => env('STORAGE_BUCKET'),
        'message_id' => env('MESSAGE_ID'),
        'app_id' => env('APP_ID'),
    ]
];
