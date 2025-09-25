<?php

// config for AgustinZamar/Wsaa
return [

    // ***********
    // Paths to your certificate and private key files
    // ***********
    'cuit' => env('ARCA_CUIT', '20111111112'),
    'private_key' => env('ARCA_PRIVATE_KEY', storage_path('app/arca/arca.key')),
    'public_cert' => env('ARCA_PUBLIC_CERT', storage_path('app/arca/arca.crt')),
    'passphrase' => env('ARCA_PASSPHRASE', null),

    // ***********
    // Cache
    // ***********
    'cache_key' => 'laravel-arca-sdk-ta',
    'cache_ttl' => 3600 * 24,

    // ***********
    // URL for the different web services
    // ***********
    'wsaa_wsdl_url' => env('ARCA_WSAA_WDSL_URL', 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms?WSDL'),
    'wsfe_wsdl_url' => env('ARCA_WSFE_WDSL_URL', 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL'),
];
