<?php

namespace AgustinZamar\LaravelArcaSdk\Clients;

use const SOAP_1_2;

use AgustinZamar\LaravelArcaSdk\Enums\WebService;
use AgustinZamar\LaravelArcaSdk\Support\ArcaUrlResolver;
use SoapClient;
use SoapFault;

abstract class ArcaClient
{
    public readonly SoapClient $client;

    public readonly WebService $service;

    public readonly WsaaClient $wsaaClient;

    public function __construct(WsaaClient $wsaaClient, WebService $service, array $options = [])
    {
        $this->wsaaClient = $wsaaClient;
        $this->service = $service;

        $this->client = $this->createSoapClient($options);
    }

    /**
     * @throws SoapFault
     */
    protected function createSoapClient(array $options = []): SoapClient
    {
        return new SoapClient(ArcaUrlResolver::getWebServiceUrl($this->service), array_merge([
            'soap_version' => SOAP_1_2,
            'trace' => 1,
            'exceptions' => true,
        ], $options));
    }

    /**
     * Call SOAP method with automatic Auth parameter injection
     */
    protected function call(string $method, array $params = []): mixed
    {
        // Automatically merge Auth params if not already present
        if (!isset($params['Auth'])) {
            $params['Auth'] = $this->getAuthParams();
        }

        return $this->client->$method($params);
    }

    protected function getAuthParams(): array
    {
        $authorizationTicket = $this->wsaaClient->getAuthorizationTicket($this->service);

        return [
            'Token' => $authorizationTicket->token,
            'Sign' => $authorizationTicket->sign,
            'Cuit' => config('laravel-arca-sdk.cuit'),
        ];
    }
}
