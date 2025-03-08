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
    | Auto-Sync Settings
    |--------------------------------------------------------------------------
    |
    | Configure how the package should automatically sync your users with Gorse.
    |
    */
    'auto_sync' => [
        'enabled' => true,
        'user_fields' => [
            'labels' => [], // Additional user fields to sync as labels
        ],
    ],
]; 