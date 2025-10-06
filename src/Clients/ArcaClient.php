<?php

namespace AgustinZamar\LaravelArcaSdk\Clients;

use const SOAP_1_2;

use AgustinZamar\LaravelArcaSdk\Enums\WebService;
use AgustinZamar\LaravelArcaSdk\Exceptions\ArcaException;
use AgustinZamar\LaravelArcaSdk\Support\ArcaErrors;
use AgustinZamar\LaravelArcaSdk\Support\ArcaUrlResolver;
use SoapClient;
use SoapFault;
use stdClass;

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
     *
     * @throws ArcaException
     */
    protected function call(string $method, array $params = []): stdClass
    {
        // Automatically merge Auth params if not already present
        if (! isset($params['Auth'])) {
            $params['Auth'] = $this->getAuthParams();
        }

        try {
            return $this->client->$method($params);
        } catch (SoapFault $e) {
            throw new ArcaException(
                message: "Error calling {$method} on {$this->service->value}: {$e->getMessage()}",
                code: $e->getCode(),
                previous: $e
            );
        }
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

    protected function hasErrors(object $result): bool
    {
        return ! empty($result->Errors);
    }

    protected function handleErrorResponse(object $result): ArcaErrors
    {
        return ArcaErrors::fromResponse($result->Errors);
    }
}
