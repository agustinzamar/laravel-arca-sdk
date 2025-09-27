<?php

namespace AgustinZamar\LaravelArcaSdk\Enums;

enum RecipientVatCondition: int
{
    case IVA_RESPONSABLE_INSCRIPTO = 1;
    case RESPONSABLE_MONOTRIBUTO = 6;
    case MONOTRIBUTISTA_SOCIAL = 13;
    case MONOTRIBUTISTA_TRABAJADOR_INDEPENDIENTE_PROMOVIDO = 16;
    case IVA_SUJETO_EXENTO = 4;
    case SUJETO_NO_CATEGORIZADO = 7;
    case PROVEEDOR_DEL_EXTERIOR = 8;
    case CLIENTE_DEL_EXTERIOR = 9;
    case IVA_LIBERADO_LEY_19640 = 10;
    case IVA_NO_ALCANZADO = 15;
    case CONSUMIDOR_FINAL = 5;

    public function getLabel(): string
    {
        return match ($this) {
            self::IVA_RESPONSABLE_INSCRIPTO => 'IVA Responsable Inscripto',
            self::RESPONSABLE_MONOTRIBUTO => 'Responsable Monotributo',
            self::MONOTRIBUTISTA_SOCIAL => 'Monotributista Social',
            self::MONOTRIBUTISTA_TRABAJADOR_INDEPENDIENTE_PROMOVIDO => 'Monotributo Trabajador Independiente Promovido',
            self::IVA_SUJETO_EXENTO => 'IVA Sujeto Exento',
            self::SUJETO_NO_CATEGORIZADO => 'Sujeto No Categorizado',
            self::PROVEEDOR_DEL_EXTERIOR => 'Proveedor del Exterior',
            self::CLIENTE_DEL_EXTERIOR => 'Cliente del Exterior',
            self::IVA_LIBERADO_LEY_19640 => 'IVA Liberado – Ley N° 19.640',
            self::IVA_NO_ALCANZADO => 'IVA No Alcanzado',
            self::CONSUMIDOR_FINAL => 'Consumidor Final',
        };
    }
}
