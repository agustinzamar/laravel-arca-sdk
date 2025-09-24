<?php

namespace AgustinZamar\LaravelArcaSdk\Facades;

use AgustinZamar\LaravelArcaSdk\WsaaClient;
use Illuminate\Support\Facades\Facade;

/**
 * @see \AgustinZamar\LaravelArcaSdk\WsaaClient
 */
class Wsaa extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return WsaaClient::class;
    }
}
