<?php

// config for AgustinZamar/Wsaa
return [

    // ***********
    // Paths to your certificate and private key files
    // ***********
    'private_key' => env('ARCA_PRIVATE_KEY', storage_path('app/arca/arca.key')),
    'public_cert' => env('ARCA_PUBLIC_CERT', storage_path('app/arca/arca.crt')),
    'passphrase' => env('ARCA_PASSPHRASE', null),


    // ***********
    // Cache
    // ***********
    'cache_ttl' => 3600 * 24,
    'cache_key' => 'arca_wsaa_token',

    // ***********
    // WSDL URL for the WSAA service
    // ***********
    'wsaa_wsdl_url' => env('ARCA_WSAA_WDSL_URL', 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms'),
];
