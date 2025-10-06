<?php

declare(strict_types=1);

use AgustinZamar\LaravelArcaSdk\Enums\WebService;
use AgustinZamar\LaravelArcaSdk\Support\ArcaUrlResolver;

it('can get url with web service enum', function () {
    // Set up configuration for testing environment
    config([
        'arca-sdk.env' => 'testing',
        'arca-sdk.wsdl_url.wsfe.testing' => 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL',
    ]);

    $url = ArcaUrlResolver::getWebServiceUrl(WebService::WSFE);

    expect($url)->toBe('https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL');
});

it('can get url with string service name', function () {
    // Set up configuration for testing environment
    config([
        'arca-sdk.env' => 'testing',
        'arca-sdk.wsdl_url.wsfe.testing' => 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL',
    ]);

    $url = ArcaUrlResolver::getWebServiceUrl('wsfe');

    expect($url)->toBe('https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL');
});

it('can get production url', function () {
    // Set up configuration for production environment
    config([
        'arca-sdk.env' => 'production',
        'arca-sdk.wsdl_url.wsfe.production' => 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL',
    ]);

    $url = ArcaUrlResolver::getWebServiceUrl(WebService::WSFE);

    expect($url)->toBe('https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL');
});

it('can get testing url', function () {
    // Set up configuration for testing environment (default)
    config([
        'arca-sdk.env' => 'testing',
        'arca-sdk.wsdl_url.wsfe.testing' => 'https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL',
    ]);

    $url = ArcaUrlResolver::getWebServiceUrl(WebService::WSFE);

    expect($url)->toBe('https://wswhomo.afip.gov.ar/wsfev1/service.asmx?WSDL');
});

it('can get wsaa service url', function () {
    // Set up configuration for wsaa service
    config([
        'arca-sdk.env' => 'testing',
        'arca-sdk.wsdl_url.wsaa.testing' => 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms?WSDL',
    ]);

    $url = ArcaUrlResolver::getWebServiceUrl('wsaa');

    expect($url)->toBe('https://wsaahomo.afip.gov.ar/ws/services/LoginCms?WSDL');
});

it('can get wsaa service url with enum', function () {
    // Set up configuration for wsaa service using enum
    config([
        'arca-sdk.env' => 'testing',
        'arca-sdk.wsdl_url.wsaa.testing' => 'https://wsaahomo.afip.gov.ar/ws/services/LoginCms?WSDL',
    ]);

    $url = ArcaUrlResolver::getWebServiceUrl(WebService::WSAA);

    expect($url)->toBe('https://wsaahomo.afip.gov.ar/ws/services/LoginCms?WSDL');
});

it('throws exception when service url not configured', function () {
    // Clear any existing configuration
    config([
        'arca-sdk.env' => 'testing',
        'arca-sdk.wsdl_url.nonexistent.testing' => null,
    ]);

    expect(fn () => ArcaUrlResolver::getWebServiceUrl('nonexistent'))
        ->toThrow(InvalidArgumentException::class, 'No URL configured for service [nonexistent] in environment [testing]');
});

it('throws exception when environment url not configured', function () {
    // Set up configuration for production but try to access testing
    config([
        'arca-sdk.env' => 'testing',
        'arca-sdk.wsdl_url.wsfe.production' => 'https://production.url',
        'arca-sdk.wsdl_url.wsfe.testing' => null,
    ]);

    expect(fn () => ArcaUrlResolver::getWebServiceUrl('wsfe'))
        ->toThrow(InvalidArgumentException::class, 'No URL configured for service [wsfe] in environment [testing]');
});

it('handles different environments', function () {
    // Test production environment
    config([
        'arca-sdk.env' => 'production',
        'arca-sdk.wsdl_url.wsfe.production' => 'https://production.example.com',
        'arca-sdk.wsdl_url.wsfe.testing' => 'https://testing.example.com',
    ]);

    $productionUrl = ArcaUrlResolver::getWebServiceUrl('wsfe');
    expect($productionUrl)->toBe('https://production.example.com');

    // Change to testing environment
    config(['arca-sdk.env' => 'testing']);

    $testingUrl = ArcaUrlResolver::getWebServiceUrl('wsfe');
    expect($testingUrl)->toBe('https://testing.example.com');
});

it('handles custom environment fallback', function () {
    // Test with custom environment value that should fallback to testing
    config([
        'arca-sdk.env' => 'development', // Non-production should fallback to testing
        'arca-sdk.wsdl_url.wsfe.testing' => 'https://testing.fallback.com',
    ]);

    $url = ArcaUrlResolver::getWebServiceUrl('wsfe');
    expect($url)->toBe('https://testing.fallback.com');
});
