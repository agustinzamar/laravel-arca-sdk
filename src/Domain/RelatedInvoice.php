<?php

declare(strict_types=1);

namespace AgustinZamar\LaravelArcaSdk\Domain;

use AgustinZamar\LaravelArcaSdk\Enums\InvoiceType;
use Illuminate\Support\Carbon;

class RelatedInvoice
{
    public function __construct(
        public readonly InvoiceType $invoiceType,
        public readonly int $pointOfSale,
        public readonly int $invoiceNumber,
        public readonly ?string $cuit = null,
        public readonly ?Carbon $invoiceDate = null,
    ) {}
}
