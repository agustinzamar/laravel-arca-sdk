<?php

declare(strict_types=1);

namespace AgustinZamar\LaravelArcaSdk\Support;

class ArcaEnvironmentResolver
{
    public static function getEnv(): string
    {
        return config('laravel-arca-sdk.env') === 'production' ? 'production' : 'testing';
    }
}
