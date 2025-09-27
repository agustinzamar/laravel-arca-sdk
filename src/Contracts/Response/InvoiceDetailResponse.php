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
use AgustinZamar\LaravelArcaSdk\Enums\RecipientVatCondition;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class InvoiceDetailResponse
{
    public function __construct(
        public InvoiceConcept        $concept,
        public Identification        $identification,
        public InvoiceType           $invoiceType,
        public int                   $pointOfSale,
        public int                   $invoiceFrom,
        public int                   $invoiceTo,
        public Carbon                $invoiceDate,
        public float                 $totalAmount,
        public float                 $untaxedAmount,
        public float                 $netAmount,
        public float                 $exemptAmount,
        public float                 $taxesAmount,
        public float                 $vatAmount,
        public ?Carbon               $serviceDateFrom,
        public ?Carbon               $serviceDateTo,
        public ?Carbon               $paymentDueDate,
        public Currency              $currencyCode,
        public float                 $currencyRate,
        public RecipientVatCondition $recipientVatCondition,
        /** @var Collection<RelatedInvoice> */
        public Collection            $relatedInvoices,
        /** @var Collection<Tax> */
        public Collection            $taxes,
        /** @var Collection<Vat> */
        public Collection            $vatItems,
        /** @var Collection<Optional> */
        public Collection            $optionals,
        /** @var Collection<Buyer> */
        public Collection            $buyers,
        public ?Carbon               $periodFrom,
        public ?Carbon               $periodTo,
        public string                $result,
        public string                $authorizationCode,
        public string                $emissionType,
        public Carbon                $authorizationCodeDueDate,
        public Carbon                $processDate,
        /** @var Collection<Observation> */
        public Collection            $observations,
    )
    {
    }
}
