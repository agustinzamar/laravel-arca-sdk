<?php

/*
|--------------------------------------------------------------------------
| Laravel ARCA SDK Service Configuration
|--------------------------------------------------------------------------
|
| This file is for storing the credentials for the ARCA services
|
*/
return [
    'env' => env('ARCA_ENV', 'testing'),

    /*
    |--------------------------------------------------------------------------
    | Authentication Credentials
    |--------------------------------------------------------------------------
    |
    | Paths to your certificate and private key files and the related CUIT
    |
    */
    'cuit' => env('ARCA_CUIT', '20111111112'),
    'directory' => env('ARCA_DIRECTORY', storage_path('private/arca')),
    'private_key' => env('ARCA_PRIVATE_KEY', 'arca.key'),
    'public_cert' => env('ARCA_PUBLIC_CERT', 'arca.crt'),
    'passphrase' => env('ARCA_PASSPHRASE', null),

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Settings for caching the authentication token
    |
    */
    'cache_key' => 'laravel-arca-sdk-ta',
    'cache_ttl' => 3600 * 12, // 12 hours

    /*
    |--------------------------------------------------------------------------
    | Web Services URLs
    |--------------------------------------------------------------------------
    |
    | URL for the different web services
    |
    */
    'wsdl_url' => [
        'wsaa' => [
            'production' => '',
            'testing' => 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms?WSDL',
        ],

        'wsfe' => [
            'production' => '',
            'testing' => 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL',
        ],
    ],
];
