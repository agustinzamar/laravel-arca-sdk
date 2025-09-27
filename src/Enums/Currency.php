<?php

namespace AgustinZamar\LaravelArcaSdk\Enums;

enum Currency: string
{
    case ARS = 'PES';

    public function getLabel(): string
    {
        return match ($this) {
            self::ARS => 'Peso Argentino',
        };
    }
}
