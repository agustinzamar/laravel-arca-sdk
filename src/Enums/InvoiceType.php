<?php

namespace AgustinZamar\LaravelArcaSdk\Enums;

enum InvoiceType: int
{
    case FACTURA_A = 1;
    case NOTA_DE_DEBITO_A = 2;
    case NOTA_DE_CREDITO_A = 3;
    case FACTURA_B = 6;
    case NOTA_DE_DEBITO_B = 7;
    case NOTA_DE_CREDITO_B = 8;
    case RECIBOS_A = 4;
    case NOTAS_DE_VENTA_AL_CONTADO_A = 5;
    case RECIBOS_B = 9;
    case NOTAS_DE_VENTA_AL_CONTADO_B = 10;
    case LIQUIDACION_A = 63;
    case LIQUIDACION_B = 64;
    case CBTES_A_ANEXO_I_1415 = 34;
    case CBTES_B_ANEXO_I_1415 = 35;
    case OTROS_COMPROBANTES_A_1415 = 39;
    case OTROS_COMPROBANTES_B_1415 = 40;
    case CTA_DE_VTA_Y_LIQUIDO_PROD_A = 60;
    case CTA_DE_VTA_Y_LIQUIDO_PROD_B = 61;
    case FACTURA_C = 11;
    case NOTA_DE_DEBITO_C = 12;
    case NOTA_DE_CREDITO_C = 13;
    case RECIBO_C = 15;
    case COMPRA_BIENES_USADOS = 49;
    case FACTURA_M = 51;
    case NOTA_DE_DEBITO_M = 52;
    case NOTA_DE_CREDITO_M = 53;
    case RECIBO_M = 54;
    case FCE_A = 201;
    case FCE_DEBITO_A = 202;
    case FCE_CREDITO_A = 203;
    case FCE_B = 206;
    case FCE_DEBITO_B = 207;
    case FCE_CREDITO_B = 208;
    case FCE_C = 211;
    case FCE_DEBITO_C = 212;
    case FCE_CREDITO_C = 213;

    public function getLabel(): string
    {
        return match ($this) {
            self::FACTURA_A => 'Factura A',
            self::NOTA_DE_DEBITO_A => 'Nota de Débito A',
            self::NOTA_DE_CREDITO_A => 'Nota de Crédito A',
            self::FACTURA_B => 'Factura B',
            self::NOTA_DE_DEBITO_B => 'Nota de Débito B',
            self::NOTA_DE_CREDITO_B => 'Nota de Crédito B',
            self::RECIBOS_A => 'Recibos A',
            self::NOTAS_DE_VENTA_AL_CONTADO_A => 'Notas de Venta al contado A',
            self::RECIBOS_B => 'Recibos B',
            self::NOTAS_DE_VENTA_AL_CONTADO_B => 'Notas de Venta al contado B',
            self::LIQUIDACION_A => 'Liquidación A',
            self::LIQUIDACION_B => 'Liquidación B',
            self::CBTES_A_ANEXO_I_1415 => 'Cbtes. A del Anexo I, Apartado A, inc. f), R.G. Nro. 1415',
            self::CBTES_B_ANEXO_I_1415 => 'Cbtes. B del Anexo I, Apartado A, inc. f), R.G. Nro. 1415',
            self::OTROS_COMPROBANTES_A_1415 => 'Otros comprobantes A que cumplan con R.G. Nro. 1415',
            self::OTROS_COMPROBANTES_B_1415 => 'Otros comprobantes B que cumplan con R.G. Nro. 1415',
            self::CTA_DE_VTA_Y_LIQUIDO_PROD_A => 'Cta de Vta y Líquido prod. A',
            self::CTA_DE_VTA_Y_LIQUIDO_PROD_B => 'Cta de Vta y Líquido prod. B',
            self::FACTURA_C => 'Factura C',
            self::NOTA_DE_DEBITO_C => 'Nota de Débito C',
            self::NOTA_DE_CREDITO_C => 'Nota de Crédito C',
            self::RECIBO_C => 'Recibo C',
            self::COMPRA_BIENES_USADOS => 'Comprobante de Compra de Bienes Usados a Consumidor Final',
            self::FACTURA_M => 'Factura M',
            self::NOTA_DE_DEBITO_M => 'Nota de Débito M',
            self::NOTA_DE_CREDITO_M => 'Nota de Crédito M',
            self::RECIBO_M => 'Recibo M',
            self::FCE_A => 'Factura de Crédito electrónica MiPyMEs (FCE) A',
            self::FCE_DEBITO_A => 'Nota de Débito electrónica MiPyMEs (FCE) A',
            self::FCE_CREDITO_A => 'Nota de Crédito electrónica MiPyMEs (FCE) A',
            self::FCE_B => 'Factura de Crédito electrónica MiPyMEs (FCE) B',
            self::FCE_DEBITO_B => 'Nota de Débito electrónica MiPyMEs (FCE) B',
            self::FCE_CREDITO_B => 'Nota de Crédito electrónica MiPyMEs (FCE) B',
            self::FCE_C => 'Factura de Crédito electrónica MiPyMEs (FCE) C',
            self::FCE_DEBITO_C => 'Nota de Débito electrónica MiPyMEs (FCE) C',
            self::FCE_CREDITO_C => 'Nota de Crédito electrónica MiPyMEs (FCE) C',
        };
    }
}
