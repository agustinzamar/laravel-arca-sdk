<?php

declare(strict_types=1);

use AgustinZamar\LaravelArcaSdk\Support\ArcaEnvironmentResolver;

it('returns production when config is set to production', function () {
    config(['arca-sdk.env' => 'production']);

    $env = ArcaEnvironmentResolver::getEnv();

    expect($env)->toBe('production');
});

it('returns testing when config is set to testing', function () {
    config(['arca-sdk.env' => 'testing']);

    $env = ArcaEnvironmentResolver::getEnv();

    expect($env)->toBe('testing');
});

it('returns testing when config is null', function () {
    config(['arca-sdk.env' => null]);

    $env = ArcaEnvironmentResolver::getEnv();

    expect($env)->toBe('testing');
});

it('returns testing when config is empty string', function () {
    config(['arca-sdk.env' => '']);

    $env = ArcaEnvironmentResolver::getEnv();

    expect($env)->toBe('testing');
});

it('returns testing for any non-production value', function () {
    $nonProductionValues = [
        'development',
        'staging',
        'local',
        'demo',
        'sandbox',
        'test',
        'prod', // Note: not exactly 'production'
        'PRODUCTION', // Case sensitive
        'Production',
        'false',
        'true',
        '0',
        '1',
    ];

    foreach ($nonProductionValues as $value) {
        config(['arca-sdk.env' => $value]);

        $env = ArcaEnvironmentResolver::getEnv();

        expect($env)->toBe('testing')
            ->and($env)->not()->toBe('production');
    }
});

it('is case sensitive for production value', function () {
    $caseVariants = [
        'PRODUCTION',
        'Production',
        'pRODUCTION',
        'production ',  // with trailing space
    ];

    foreach ($caseVariants as $variant) {
        config(['arca-sdk.env' => $variant]);

        $env = ArcaEnvironmentResolver::getEnv();

        expect($env)->toBe('testing')
            ->and($env)->not()->toBe('production');
    }
});

it('handles boolean values correctly', function () {
    // Test boolean true
    config(['arca-sdk.env' => true]);
    expect(ArcaEnvironmentResolver::getEnv())->toBe('testing');

    // Test boolean false
    config(['arca-sdk.env' => false]);
    expect(ArcaEnvironmentResolver::getEnv())->toBe('testing');
});

it('handles numeric values correctly', function () {
    // Test integer
    config(['arca-sdk.env' => 1]);
    expect(ArcaEnvironmentResolver::getEnv())->toBe('testing');

    // Test zero
    config(['arca-sdk.env' => 0]);
    expect(ArcaEnvironmentResolver::getEnv())->toBe('testing');

    // Test float
    config(['arca-sdk.env' => 1.5]);
    expect(ArcaEnvironmentResolver::getEnv())->toBe('testing');
});

it('uses default config value when not explicitly set', function () {
    // Clear the config to test default behavior
    config(['laravel-arca-sdk' => []]);

    $env = ArcaEnvironmentResolver::getEnv();

    // Should fall back to testing since config default is 'testing'
    expect($env)->toBe('testing');
});

it('works with exact production string match', function () {
    config(['arca-sdk.env' => 'production']);

    expect(ArcaEnvironmentResolver::getEnv())->toBe('production');

    // Verify it's exactly matching the string
    config(['arca-sdk.env' => 'productioN']);
    expect(ArcaEnvironmentResolver::getEnv())->toBe('testing');
});
