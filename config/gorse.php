<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Gorse API Key
    |--------------------------------------------------------------------------
    |
    | This is the API key that will be used to authenticate with the Gorse API.
    | You can find this in your Gorse dashboard.
    |
    */
    'api_key' => env('GORSE_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Gorse API Endpoint
    |--------------------------------------------------------------------------
    |
    | This is the endpoint where your Gorse instance is running. Make sure to
    | include the protocol (http/https) and port if necessary.
    |
    */
    'endpoint' => env('GORSE_ENDPOINT', 'http://localhost:8087'),

    /*
    |--------------------------------------------------------------------------
    | SSL Verification
    |--------------------------------------------------------------------------
    |
    | For development environments or when using self-signed certificates,
    | you may want to disable SSL certificate verification. 
    | It's recommended to keep this enabled in production.
    |
    */
    'verify_ssl' => env('GORSE_VERIFY_SSL', true),

    /*
    |--------------------------------------------------------------------------
    | Auto-Sync Settings
    |--------------------------------------------------------------------------
    |
    | Configure how the package should automatically sync your users with Gorse.
    |
    */
    'auto_sync' => [
        'enabled' => env('GORSE_AUTO_SYNC', false),
        'user_fields' => [
            'labels' => [], // Additional user fields to sync as labels
        ],
    ],
]; 