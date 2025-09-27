<?php

declare(strict_types=1);

use AgustinZamar\LaravelArcaSdk\Enums\WebService;
use AgustinZamar\LaravelArcaSdk\Support\ArcaUrlResolver;

it('can get url with web service enum', function () {
    // Set up configuration for testing environment
    config([
        'laravel-arca-sdk.env' => 'testing',
        'laravel-arca-sdk.wsfe.testing' => 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL'
    ]);

    $url = ArcaUrlResolver::getWebServiceUrl(WebService::WSFE);

    expect($url)->toBe('https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL');
});

it('can get url with string service name', function () {
    // Set up configuration for testing environment
    config([
        'laravel-arca-sdk.env' => 'testing',
        'laravel-arca-sdk.wsfe.testing' => 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL'
    ]);

    $url = ArcaUrlResolver::getWebServiceUrl('wsfe');

    expect($url)->toBe('https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL');
});

it('can get production url', function () {
    // Set up configuration for production environment
    config([
        'laravel-arca-sdk.env' => 'production',
        'laravel-arca-sdk.wsfe.production' => 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL'
    ]);

    $url = ArcaUrlResolver::getWebServiceUrl(WebService::WSFE);

    expect($url)->toBe('https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL');
});

it('can get testing url', function () {
    // Set up configuration for testing environment (default)
    config([
        'laravel-arca-sdk.env' => 'testing',
        'laravel-arca-sdk.wsfe.testing' => 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL'
    ]);

    $url = ArcaUrlResolver::getWebServiceUrl(WebService::WSFE);

    expect($url)->toBe('https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL');
});

it('can get wsaa service url', function () {
    // Set up configuration for wsaa service
    config([
        'laravel-arca-sdk.env' => 'testing',
        'laravel-arca-sdk.wsaa.testing' => 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms?WSDL'
    ]);

    $url = ArcaUrlResolver::getWebServiceUrl('wsaa');

    expect($url)->toBe('https://wsaahomo.afip.gov.ar/ws/services/LoginCms?WSDL');
});

it('throws exception when service url not configured', function () {
    // Clear any existing configuration
    config([
        'laravel-arca-sdk.env' => 'testing',
        'laravel-arca-sdk.nonexistent.testing' => null
    ]);

    expect(fn() => ArcaUrlResolver::getWebServiceUrl('nonexistent'))
        ->toThrow(InvalidArgumentException::class, 'No URL configured for service [nonexistent] in environment [testing]');
});

it('throws exception when environment url not configured', function () {
    // Set up configuration for production but try to access testing
    config([
        'laravel-arca-sdk.env' => 'testing',
        'laravel-arca-sdk.wsfe.production' => 'https://production.url',
        'laravel-arca-sdk.wsfe.testing' => null
    ]);

    expect(fn() => ArcaUrlResolver::getWebServiceUrl('wsfe'))
        ->toThrow(InvalidArgumentException::class, 'No URL configured for service [wsfe] in environment [testing]');
});

it('handles different environments', function () {
    // Test production environment
    config([
        'laravel-arca-sdk.env' => 'production',
        'laravel-arca-sdk.wsfe.production' => 'https://production.example.com',
        'laravel-arca-sdk.wsfe.testing' => 'https://testing.example.com'
    ]);

    $productionUrl = ArcaUrlResolver::getWebServiceUrl('wsfe');
    expect($productionUrl)->toBe('https://production.example.com');

    // Change to testing environment
    config(['laravel-arca-sdk.env' => 'testing']);

    $testingUrl = ArcaUrlResolver::getWebServiceUrl('wsfe');
    expect($testingUrl)->toBe('https://testing.example.com');
});

it('handles custom environment fallback', function () {
    // Test with custom environment value that should fallback to testing
    config([
        'laravel-arca-sdk.env' => 'development', // Non-production should fallback to testing
        'laravel-arca-sdk.wsfe.testing' => 'https://testing.fallback.com'
    ]);

    $url = ArcaUrlResolver::getWebServiceUrl('wsfe');
    expect($url)->toBe('https://testing.fallback.com');
});
