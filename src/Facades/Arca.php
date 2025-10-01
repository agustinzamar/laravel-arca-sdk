<?php

namespace AgustinZamar\LaravelArcaSdk\Facades;

use AgustinZamar\LaravelArcaSdk\LaravelArcaSdk;
use Illuminate\Support\Facades\Facade;

/**
 * @see LaravelArcaSdk
 */
class Arca extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return LaravelArcaSdk::class;
    }
}
