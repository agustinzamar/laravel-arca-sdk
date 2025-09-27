<?php

declare(strict_types=1);

namespace AgustinZamar\LaravelArcaSdk\Domain;

use AgustinZamar\LaravelArcaSdk\Enums\InvoiceType;

class RelatedInvoice
{
    public function __construct(
        public InvoiceType $invoiceType,
        public int         $pointOfSale,
        public int         $invoiceNumber,
    )
    {
    }
}
