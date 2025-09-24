<?php

declare(strict_types=1);

namespace AgustinZamar\LaravelArcaSdk\Domain;

use AgustinZamar\LaravelArcaSdk\Enums\InvoiceConcept;
use Carbon\Carbon;

class Invoice
{
    public function __construct(
        public readonly InvoiceConcept $concept,
        public readonly Identification $identification,
        public readonly int            $invoiceFrom,
        public readonly int            $invoiceTo,
        public readonly Carbon         $invoiceDate,
        public readonly string         $cae,
        public readonly Carbon         $caeExpirationDate
    )
    {
    }
}
