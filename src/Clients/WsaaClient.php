<?php

namespace AgustinZamar\LaravelArcaSdk\Clients;

use AgustinZamar\LaravelArcaSdk\Domain\AuthorizationTicket;
use AgustinZamar\LaravelArcaSdk\Enums\WebService;
use AgustinZamar\LaravelArcaSdk\Support\ArcaUrlResolver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;
use SoapClient;

class WsaaClient
{
    const TA_FILE = 'app/arca/TA.xml';

    const TRA_FILE = 'app/arca/TRA.xml';

    const TRA_TEMP_FILE = 'app/arca/TRA.tmp';

    public function getAuthorizationTicket(WebService|string $service): AuthorizationTicket
    {
        $service = $service instanceof WebService ? $service->value : $service;
        $cacheKey = $this->cacheKey().'-'.$service;

        return Cache::remember($cacheKey, $this->ttl(), function () use ($service) {
            $cms = $this->signTra($this->createTra($service));
            $taXml = $this->callLaravelArcaSdk($cms);

            Storage::disk('local')->put(self::TA_FILE, $taXml);

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

        $path = storage_path(self::TRA_FILE);
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true); // true = crea recursivamente
        }

        $xml->asXML($path);

        return $path;
    }

    protected function signTra(string $traPath): string
    {
        $tmpPath = storage_path(self::TRA_TEMP_FILE);
        $status = openssl_pkcs7_sign(
            $traPath,
            $tmpPath,
            'file://'.config('laravel-arca-sdk.public_cert'),
            ['file://'.config('laravel-arca-sdk.private_key'), config('laravel-arca-sdk.passphrase')],
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

    protected function callLaravelArcaSdk(string $cms): string
    {
        $client = new SoapClient(ArcaUrlResolver::getWebServiceUrl(WebService::WSAA), [
            'soap_version' => SOAP_1_2,
            'trace' => 1,
            'exceptions' => true,
        ]);

        $result = $client->loginCms(['in0' => $cms]);

        return $result->loginCmsReturn;
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
