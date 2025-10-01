<?php

declare(strict_types=1);

namespace AgustinZamar\LaravelArcaSdk\Support;

use const PHP_OS_FAMILY;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Class for generating SSL certificates with OpenSSL
 */
class CertificateRequestGenerator
{
    private string $keyFileName = 'arca.key';

    private string $csrFileName = 'arca.csr';

    private string $directoryPath;

    /**
     * Constructor
     *
     * @param  string  $organizationName  Organization name
     * @param  string  $applicationName  Application name (Common Name)
     * @param  string  $cuit  CUIT number (with or without hyphens)
     */
    public function __construct(public readonly string $organizationName, public readonly string $applicationName, public readonly string $cuit)
    {
        $this->directoryPath = 'arca/certificates/'.$this->getCleanCuit();

        if (PHP_OS_FAMILY === 'Windows') {
            throw new RuntimeException('This class is not supported on Windows systems yet.');
        }
    }

    /**
     * Creates the directory for certificates
     *
     * @throws RuntimeException If directory cannot be created
     * @throws Throwable
     */
    private function createDirectory(): void
    {
        if (! Storage::exists($this->directoryPath)) {
            throw_unless(
                Storage::makeDirectory($this->directoryPath),
                RuntimeException::class,
                "Could not create directory: {$this->directoryPath}"
            );
        }
    }

    /**
     * Generates the RSA private key
     *
     * @throws RuntimeException|Throwable If there's an error generating the key
     */
    private function generatePrivateKey(): void
    {
        $keyPath = Storage::path($this->getKeyPath());

        $result = Process::run("openssl genrsa -out {$keyPath} 2048");

        throw_if(
            $result->failed(),
            RuntimeException::class,
            "Error generating private key: {$result->errorOutput()}"
        );

        throw_unless(
            Storage::exists($this->getKeyPath()),
            RuntimeException::class,
            'The .key file was not created correctly'
        );
    }

    /**
     * Generates the Certificate Signing Request (CSR)
     *
     * @throws RuntimeException|Throwable If there's an error generating the CSR
     */
    private function generateCSR(): void
    {
        $keyPath = Storage::path($this->getKeyPath());
        $csrPath = Storage::path($this->getCsrPath());

        $subject = collect([
            'C' => 'AR',
            'O' => $this->organizationName,
            'CN' => $this->applicationName,
            'serialNumber' => "CUIT {$this->getCleanCuit()}",
        ])->map(fn ($value, $key) => "{$key}=".$this->escapeSubjectValue($value))
            ->implode('/');

        $command = "openssl req -new -key {$keyPath} -subj \"/{$subject}\" -out {$csrPath}";

        $result = Process::run($command);

        throw_if(
            $result->failed(),
            RuntimeException::class,
            "Error generating CSR: {$result->errorOutput()}"
        );

        throw_unless(
            Storage::exists($this->getCsrPath()),
            RuntimeException::class,
            'The .csr file was not created correctly'
        );
    }

    /**
     * Reads the content of the CSR file
     *
     * @return string CSR file content
     *
     * @throws RuntimeException|Throwable If the file cannot be read
     */
    private function readCSRContent(): string
    {
        throw_unless(
            Storage::exists($this->getCsrPath()),
            RuntimeException::class,
            'CSR file does not exist'
        );

        return Storage::get($this->getCsrPath());
    }

    /**
     * Escape special characters in subject values for OpenSSL
     */
    private function escapeSubjectValue(string $value): string
    {
        // Escape special characters that have meaning in OpenSSL subject strings
        // These include: / \ , = + < > # ;
        return addcslashes($value, '/\\,=+<>#;');
    }

    /**
     * Generates the certificates and returns the CSR content
     *
     * @return string Content of the .csr file as plain text
     *
     * @throws RuntimeException|Throwable If there are errors during generation
     */
    public function generate(): string
    {
        try {
            $this->createDirectory();
            $this->generatePrivateKey();
            $this->generateCSR();

            return $this->readCSRContent();
        } catch (Throwable $e) {
            throw new RuntimeException("Certificate generation failed: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Gets the directory path where files were saved
     *
     * @return string Directory path
     */
    public function getDirectory(): string
    {
        return Storage::path($this->directoryPath);
    }

    /**
     * Gets the .key file relative path
     *
     * @return string .key file path
     */
    public function getKeyPath(): string
    {
        return "{$this->directoryPath}/{$this->keyFileName}";
    }

    /**
     * Gets the .csr file relative path
     *
     * @return string .csr file path
     */
    public function getCsrPath(): string
    {
        return "{$this->directoryPath}/{$this->csrFileName}";
    }

    /**
     * Gets the clean CUIT (numbers only)
     *
     * @return string CUIT without special characters
     */
    public function getCleanCuit(): string
    {
        return Str::of($this->cuit)->replaceMatches('/[^0-9]/', '')->toString();
    }

    /**
     * Static factory method
     */
    public static function make(string $organizationName, string $applicationName, string $cuit): static
    {
        return new static($organizationName, $applicationName, $cuit);
    }
}
