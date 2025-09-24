<?php

namespace AgustinZamar\LaravelArcaSdk\Facades;

use AgustinZamar\LaravelArcaSdk\ArcaService;
use Illuminate\Support\Facades\Facade;

/**
 * @see \AgustinZamar\LaravelArcaSdk\WsaaClient
 */
class Arca extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ArcaService::class;
    }
}