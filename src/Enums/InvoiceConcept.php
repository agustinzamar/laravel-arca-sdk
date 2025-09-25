<?php

namespace AgustinZamar\LaravelArcaSdk\Enums;

enum InvoiceConcept: int
{
    case GOODS = 1;
    case SERVICES = 2;
    case GOODS_AND_SERVICES = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::GOODS => 'Productos',
            self::SERVICES => 'Servicios',
            self::GOODS_AND_SERVICES => 'Productos y servicios',
        };
    }
}
