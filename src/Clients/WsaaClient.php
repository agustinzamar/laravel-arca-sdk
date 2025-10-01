<?php

namespace AgustinZamar\LaravelArcaSdk\Clients;

use AgustinZamar\LaravelArcaSdk\Domain\AuthorizationTicket;
use AgustinZamar\LaravelArcaSdk\Enums\WebService;
use AgustinZamar\LaravelArcaSdk\Support\ArcaUrlResolver;
use Illuminate\Support\Facades\Cache;
use SimpleXMLElement;
use SoapClient;

class WsaaClient
{
    public function getAuthorizationTicket(WebService|string $service): AuthorizationTicket
    {
        $service = $service instanceof WebService ? $service->value : $service;
        $cacheKey = $this->cacheKey().'-'.$service;

        return Cache::remember($cacheKey, $this->ttl(), function () use ($service) {
            $cms = $this->signTra($this->createTra($service));
            $taXml = $this->requestLogin($cms);

            $taPath = $this->getFilePath('TA.xml');

            // Ensure directory exists
            if (! is_dir(dirname($taPath))) {
                mkdir(dirname($taPath), 0755, true);
            }

            file_put_contents($taPath, $taXml);

            $ta = new SimpleXMLElement($taXml);

            return new AuthorizationTicket(
                (string) $ta->credentials->token,
                (string) $ta->credentials->sign,
                (string) $ta->header->expirationTime,
            );
        });
    }

    protected function createTra(WebService|string $service): string
    {
        $service = $service instanceof WebService ? $service->value : $service;

        $xml = new SimpleXMLElement('<loginTicketRequest version="1.0"/>');
        $header = $xml->addChild('header');
        $header->addChild('uniqueId', time());
        $header->addChild('generationTime', gmdate('c', time() - 60));
        $header->addChild('expirationTime', gmdate('c', time() + config('laravel-arca-sdk.cache_ttl')));

        $xml->addChild('service', $service);

        $path = $this->getFilePath('TRA.xml');
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true); // true = crea recursivamente
        }

        $xml->asXML($path);

        return $path;
    }

    protected function signTra(string $traPath): string
    {
        $tmpPath = $this->getFilePath('TRA.tmp');
        $certificatePath = $this->getFilePath(config('laravel-arca-sdk.public_cert'));
        $keyPath = $this->getFilePath(config('laravel-arca-sdk.private_key'));
        $status = openssl_pkcs7_sign(
            $traPath,
            $tmpPath,
            'file://'.$certificatePath,
            ['file://'.$keyPath, config('laravel-arca-sdk.passphrase')],
            [],
            ! PKCS7_DETACHED
        );

        if (! $status) {
            throw new \Exception('Error signing TRA');
        }

        $cms = '';
        $fh = fopen($tmpPath, 'r');
        $i = 0;
        while (! feof($fh)) {
            $line = fgets($fh);
            if ($i++ >= 4) {
                $cms .= $line;
            }
        }
        fclose($fh);
        unlink($tmpPath);

        return $cms;
    }

    protected function requestLogin(string $cms): string
    {
        $client = new SoapClient(ArcaUrlResolver::getWebServiceUrl(WebService::WSAA), [
            'soap_version' => SOAP_1_2,
            'trace' => 1,
            'exceptions' => true,
        ]);

        $result = $client->loginCms(['in0' => $cms]);

        return $result->loginCmsReturn;
    }

    protected function getFilePath(string $filename): string
    {
        $directory = config('laravel-arca-sdk.directory');

        return rtrim($directory, '/').'/'.$filename;
    }

    protected function cacheKey(): string
    {
        return config('laravel-arca-sdk.cache_key');
    }

    protected function ttl(): int
    {
        return config('laravel-arca-sdk.cache_ttl');
    }
}
