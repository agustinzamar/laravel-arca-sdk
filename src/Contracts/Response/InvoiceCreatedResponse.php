<?php

declare(strict_types=1);

namespace AgustinZamar\LaravelArcaSdk\Contracts\Response;

use AgustinZamar\LaravelArcaSdk\Domain\Identification;
use AgustinZamar\LaravelArcaSdk\Enums\InvoiceConcept;
use Illuminate\Support\Carbon;

class InvoiceCreatedResponse
{
    public function __construct(
        public readonly InvoiceConcept $concept,
        public readonly Identification $identification,
        public readonly int $invoiceFrom,
        public readonly int $invoiceTo,
        public readonly Carbon $invoiceDate,
        public readonly string $cae,
        public readonly Carbon $caeExpirationDate,
    ) {}
}
