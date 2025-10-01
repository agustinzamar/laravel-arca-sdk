<?php

declare(strict_types=1);

use AgustinZamar\LaravelArcaSdk\Support\CertificateRequestGenerator;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

it('can be instantiated with required parameters', function () {
    $generator = new CertificateRequestGenerator(
        'Test Organization',
        'Test Application',
        '20-12345678-9'
    );

    expect($generator)->toBeInstanceOf(CertificateRequestGenerator::class);
    expect($generator->organizationName)->toBe('Test Organization');
    expect($generator->applicationName)->toBe('Test Application');
    expect($generator->cuit)->toBe('20-12345678-9');
});

it('can be instantiated using static make method', function () {
    $generator = CertificateRequestGenerator::make(
        'Test Organization',
        'Test Application',
        '20-12345678-9'
    );

    expect($generator)->toBeInstanceOf(CertificateRequestGenerator::class);
    expect($generator->organizationName)->toBe('Test Organization');
    expect($generator->applicationName)->toBe('Test Application');
    expect($generator->cuit)->toBe('20-12345678-9');
});

it('returns clean CUIT without special characters', function () {
    $generator = new CertificateRequestGenerator(
        'Test Organization',
        'Test Application',
        '20-12345678-9'
    );

    expect($generator->getCleanCuit())->toBe('20123456789');
});

it('handles CUIT with different formats', function () {
    $testCases = [
        ['20-12345678-9', '20123456789'],
        ['20123456789', '20123456789'],
        ['20.12345678.9', '20123456789'],
        ['20 12345678 9', '20123456789'],
        ['20_12345678_9', '20123456789'],
    ];

    foreach ($testCases as [$input, $expected]) {
        $generator = new CertificateRequestGenerator(
            'Test Organization',
            'Test Application',
            $input
        );

        expect($generator->getCleanCuit())->toBe($expected);
    }
});

it('generates correct file paths', function () {
    $generator = new CertificateRequestGenerator(
        'Test Organization',
        'Test Application',
        '20-12345678-9'
    );

    expect($generator->getKeyPath())->toBe('arca/certificates/20123456789/arca.key');
    expect($generator->getCsrPath())->toBe('arca/certificates/20123456789/arca.csr');
});

it('generates correct directory path', function () {
    Storage::fake('local');

    $generator = new CertificateRequestGenerator(
        'Test Organization',
        'Test Application',
        '20-12345678-9'
    );

    $expectedPath = Storage::path('arca/certificates/20123456789');
    expect($generator->getDirectory())->toBe($expectedPath);
});

it('successfully generates certificates when OpenSSL commands succeed', function () {
    Storage::fake('local');

    // Mock successful process execution
    Process::fake([
        'openssl genrsa *' => Process::result(output: 'key generated', exitCode: 0),
        'openssl req *' => Process::result(output: 'csr generated', exitCode: 0),
    ]);

    // Mock the file creation
    $generator = new CertificateRequestGenerator(
        'Test Organization',
        'Test Application',
        '20-12345678-9'
    );

    // Create mock files that would be created by OpenSSL
    Storage::put($generator->getKeyPath(), 'mock private key content');
    Storage::put($generator->getCsrPath(), 'mock csr content');

    $result = $generator->generate();

    expect($result)->toBe('mock csr content');
    expect(Storage::exists($generator->getKeyPath()))->toBeTrue();
    expect(Storage::exists($generator->getCsrPath()))->toBeTrue();
});

it('throws exception when private key generation fails', function () {
    Storage::fake('local');

    // Mock failed process execution for key generation
    Process::fake([
        'openssl genrsa *' => Process::result(errorOutput: 'key generation failed', exitCode: 1),
    ]);

    $generator = new CertificateRequestGenerator(
        'Test Organization',
        'Test Application',
        '20-12345678-9'
    );

    expect(fn () => $generator->generate())
        ->toThrow(RuntimeException::class, 'Error generating private key: key generation failed');
});

it('throws exception when CSR generation fails', function () {
    Storage::fake('local');

    // Mock successful key generation but failed CSR generation
    Process::fake([
        'openssl genrsa *' => Process::result(output: 'key generated', exitCode: 0),
        'openssl req *' => Process::result(errorOutput: 'csr generation failed', exitCode: 1),
    ]);

    $generator = new CertificateRequestGenerator(
        'Test Organization',
        'Test Application',
        '20-12345678-9'
    );

    // Create mock key file
    Storage::put($generator->getKeyPath(), 'mock private key content');

    expect(fn () => $generator->generate())
        ->toThrow(RuntimeException::class, 'Error generating CSR: csr generation failed');
});

it('throws exception when key file is not created', function () {
    Storage::fake('local');

    // Mock successful process but file is not created
    Process::fake([
        'openssl genrsa *' => Process::result(output: 'key generated', exitCode: 0),
    ]);

    $generator = new CertificateRequestGenerator(
        'Test Organization',
        'Test Application',
        '20-12345678-9'
    );

    expect(fn () => $generator->generate())
        ->toThrow(RuntimeException::class, 'The .key file was not created correctly');
});

it('throws exception when CSR file is not created', function () {
    Storage::fake('local');

    // Mock successful processes but CSR file is not created
    Process::fake([
        'openssl genrsa *' => Process::result(output: 'key generated', exitCode: 0),
        'openssl req *' => Process::result(output: 'csr generated', exitCode: 0),
    ]);

    $generator = new CertificateRequestGenerator(
        'Test Organization',
        'Test Application',
        '20-12345678-9'
    );

    // Create mock key file but not CSR file
    Storage::put($generator->getKeyPath(), 'mock private key content');

    expect(fn () => $generator->generate())
        ->toThrow(RuntimeException::class, 'The .csr file was not created correctly');
});

it('throws exception when CSR file does not exist during reading', function () {
    $generator = new CertificateRequestGenerator(
        'Test Organization',
        'Test Application',
        '20-12345678-9'
    );

    // Use reflection to test private method
    $reflection = new ReflectionClass($generator);
    $method = $reflection->getMethod('readCSRContent');
    $method->setAccessible(true);

    expect(fn () => $method->invoke($generator))
        ->toThrow(RuntimeException::class, 'CSR file does not exist');
});

it('handles special characters in organization name correctly', function () {
    $generator = new CertificateRequestGenerator(
        'Test/Organization\\With,Special=Characters+<>#;',
        'Test Application',
        '20-12345678-9'
    );

    // Use reflection to test private method
    $reflection = new ReflectionClass($generator);
    $method = $reflection->getMethod('escapeSubjectValue');
    $method->setAccessible(true);

    $escaped = $method->invoke($generator, 'Test/Organization\\With,Special=Characters+<>#;');

    expect($escaped)->toBe('Test\\/Organization\\\\With\\,Special\\=Characters\\+\\<\\>\\#\\;');
});

it('creates directory when it does not exist', function () {
    Storage::fake('local');

    $generator = new CertificateRequestGenerator(
        'Test Organization',
        'Test Application',
        '20-12345678-9'
    );

    // Use reflection to test private method
    $reflection = new ReflectionClass($generator);
    $method = $reflection->getMethod('createDirectory');
    $method->setAccessible(true);

    expect(Storage::exists('arca/certificates/20123456789'))->toBeFalse();

    $method->invoke($generator);

    expect(Storage::exists('arca/certificates/20123456789'))->toBeTrue();
});

it('does not throw when directory already exists', function () {
    Storage::fake('local');

    $generator = new CertificateRequestGenerator(
        'Test Organization',
        'Test Application',
        '20-12345678-9'
    );

    // Create directory first
    Storage::makeDirectory('arca/certificates/20123456789');

    // Use reflection to test private method
    $reflection = new ReflectionClass($generator);
    $method = $reflection->getMethod('createDirectory');
    $method->setAccessible(true);

    expect(fn () => $method->invoke($generator))->not->toThrow(RuntimeException::class);
});

it('wraps exceptions in RuntimeException during generation', function () {
    Storage::fake('local');

    // Mock process to throw an exception
    Process::fake([
        'openssl genrsa *' => fn () => throw new Exception('Unexpected error'),
    ]);

    $generator = new CertificateRequestGenerator(
        'Test Organization',
        'Test Application',
        '20-12345678-9'
    );

    expect(fn () => $generator->generate())
        ->toThrow(RuntimeException::class, 'Certificate generation failed: Unexpected error');
});
