<?php

declare(strict_types=1);

namespace AgustinZamar\LaravelArcaSdk\Contracts\Response;

use AgustinZamar\LaravelArcaSdk\Domain\Buyer;
use AgustinZamar\LaravelArcaSdk\Domain\Identification;
use AgustinZamar\LaravelArcaSdk\Domain\Observation;
use AgustinZamar\LaravelArcaSdk\Domain\Optional;
use AgustinZamar\LaravelArcaSdk\Domain\RelatedInvoice;
use AgustinZamar\LaravelArcaSdk\Domain\Tax;
use AgustinZamar\LaravelArcaSdk\Domain\Vat;
use AgustinZamar\LaravelArcaSdk\Enums\Currency;
use AgustinZamar\LaravelArcaSdk\Enums\InvoiceConcept;
use AgustinZamar\LaravelArcaSdk\Enums\InvoiceType;
use Illuminate\Support\Carbon;

class InvoiceDetailResponse
{
    public function __construct(
        public InvoiceConcept $concept,
        public Identification $identification,
        public InvoiceType    $invoiceType,
        public int            $pointOfSale,
        public int            $invoiceFrom,
        public int            $invoiceTo,
        public Carbon         $invoiceDate,
        public float          $totalAmount,
        public float          $untaxedAmount,
        public float          $netAmount,
        public float          $exemptAmount,
        public float          $taxesAmount,
        public float          $vatAmount,
        public ?Carbon        $serviceDateFrom,
        public ?Carbon        $serviceDateTo,
        public ?Carbon        $paymentDueDate,
        public Currency       $currencyCode,
        public float          $currencyRate,
        /** @var RelatedInvoice[] */
        public array          $associatedReceipts,
        /** @var Tax[] */
        public array          $taxes,
        /** @var Vat[] */
        public array          $vatItems,
        /** @var Optional[] */
        public array          $optionals,
        /** @var Buyer[] */
        public array          $buyers,
        public string         $periodFrom,
        public string         $periodTo,
        public string         $result,
        public string         $authorizationCode,
        public string         $emissionType,
        public string         $dueDate,
        public string         $processDate,
        /** @var Observation[] */
        public array          $observations,
    )
    {
    }
}
