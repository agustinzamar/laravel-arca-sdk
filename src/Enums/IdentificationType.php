<?php

namespace AgustinZamar\LaravelArcaSdk\Enums;

enum IdentificationType: string
{
    case CUIT = '80';
    case CUIL = '86';
    case DNI = '96';
    case PASSPORT = '94';
    case FOREIGN_ID = '91';

    public function getLabel(): string
    {
        return match ($this) {
            self::CUIT => 'CUIT',
            self::CUIL => 'CUIL',
            self::DNI => 'DNI',
            self::PASSPORT => 'Pasaporte',
            self::FOREIGN_ID => 'Identificación Extranjera',
        };
    }
}
