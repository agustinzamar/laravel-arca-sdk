<?php

declare(strict_types=1);

namespace AgustinZamar\LaravelArcaSdk\Support;

use AgustinZamar\LaravelArcaSdk\Enums\WebService;

class ArcaUrlResolver
{
    public static function getWebServiceUrl(WebService|string $service): string
    {
        $service = $service instanceof WebService ? $service->value : $service;
        $env = ArcaEnvironmentResolver::getEnv();
        $url = config("laravel-arca-sdk.wsdl_url.$service.$env");

        if (! $url) {
            throw new \InvalidArgumentException("No URL configured for service [$service] in environment [$env]");
        }

        return $url;
    }
}
