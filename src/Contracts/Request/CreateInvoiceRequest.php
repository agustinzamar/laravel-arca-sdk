<?php

namespace AgustinZamar\LaravelArcaSdk\Contracts\Request;

use AgustinZamar\LaravelArcaSdk\Domain\Buyer;
use AgustinZamar\LaravelArcaSdk\Domain\Identification;
use AgustinZamar\LaravelArcaSdk\Domain\Optional;
use AgustinZamar\LaravelArcaSdk\Domain\RelatedInvoice;
use AgustinZamar\LaravelArcaSdk\Domain\Tax;
use AgustinZamar\LaravelArcaSdk\Domain\Vat;
use AgustinZamar\LaravelArcaSdk\Enums\Currency;
use AgustinZamar\LaravelArcaSdk\Enums\InvoiceConcept;
use AgustinZamar\LaravelArcaSdk\Enums\InvoiceType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CreateInvoiceRequest
{

    public function __construct(
        public readonly InvoiceConcept $concept,
        public readonly int            $pointOfSale,
        public readonly Identification $identification,
        public readonly InvoiceType    $invoiceType,
        public readonly int            $invoiceFrom,
        public readonly int            $invoiceTo,
        public readonly float          $total,
        public readonly float          $net,
        public readonly float          $exempt = 0,
        public readonly int            $vatCondition,
        public readonly ?float         $nonTaxableConceptsAmount = null,
        public readonly ?Carbon        $serviceFrom = null,
        public readonly ?Carbon        $serviceTo = null,
        public readonly ?Carbon        $dueDate = null,
        public readonly Currency       $currency = Currency::ARS,
        public readonly float          $currencyQuote = 1,
        public readonly ?string        $foreignCurrencyAmount = null,
        public readonly ?Carbon        $invoiceDate = null,
        /** @var Collection<Tax> */
        public readonly ?Collection    $taxes = new Collection,
        /** @var Collection<Vat> */
        public readonly ?Collection    $vatRates = new Collection,
        /** @var Collection<RelatedInvoice> */
        public readonly ?Collection    $relatedInvoices = new Collection,
        /** @var Collection<Optional> */
        public readonly ?Collection    $optionals = new Collection,
        /** @var Collection<Buyer> */
        public readonly ?Collection    $buyers = new Collection,
        public readonly ?Carbon        $periodFrom = null,
        public readonly ?Carbon        $periodTo = null,
        /** @var Collection<int> */
        public readonly ?Collection    $activities = new Collection,
    )
    {
    }

    public function withInvoiceRange(int $from, int $to): self
    {
        return new self(
            concept: $this->concept,
            pointOfSale: $this->pointOfSale,
            identification: $this->identification,
            invoiceType: $this->invoiceType,
            invoiceFrom: $from,
            invoiceTo: $to,
            total: $this->total,
            net: $this->net,
            exempt: $this->exempt,
            vatCondition: $this->vatCondition,
            nonTaxableConceptsAmount: $this->nonTaxableConceptsAmount,
            serviceFrom: $this->serviceFrom,
            serviceTo: $this->serviceTo,
            dueDate: $this->dueDate,
            currency: $this->currency,
            currencyQuote: $this->currencyQuote,
            foreignCurrencyAmount: $this->foreignCurrencyAmount,
            invoiceDate: $this->invoiceDate,
            taxes: $this->taxes,
            vatRates: $this->vatRates,
            relatedInvoices: $this->relatedInvoices,
            optionals: $this->optionals,
            buyers: $this->buyers,
            periodFrom: $this->periodFrom,
            periodTo: $this->periodTo,
            activities: $this->activities,
        );
    }

    public function toArray(): array
    {
        $ivaArray = $this->vatRates->map(fn(Vat $vat) => [
            'Id' => $vat->id,
            'BaseImp' => $vat->baseAmount,
            'Importe' => $vat->amount,
        ])->toArray();

        $taxArray = $this->taxes->map(fn(Tax $t) => [
            'Id' => $t->id,
            'Desc' => $t->description,
            'BaseImp' => $t->baseAmount,
            'Alic' => $t->rate,
            'Importe' => $t->amount,
        ])->toArray();

        $relatedInvoicesArray = $this->relatedInvoices->map(fn(RelatedInvoice $r) => [
            'Tipo' => $r->invoiceType->value,
            'PtoVta' => $r->pointOfSale,
            'Nro' => $r->invoiceNumber,
            'Cuit' => $r->cuit,
            'CbteFch' => $r->invoiceDate?->format('Ymd'),
        ])->toArray();

        $optionalsArray = $this->optionals->map(fn(Optional $o) => [
            'Id' => $o->id,
            'Valor' => $o->value,
        ])->toArray();

        $buyersArray = $this->buyers->map(fn(Buyer $b) => [
            'DocTipo' => $b->identification->type->value,
            'DocNro' => $b->identification->number,
            'Porcentaje' => $b->percentage,
        ])->toArray();

        return [
            'FeCAEReq' => [
                'FeCabReq' => [
                    'CantReg' => 1,
                    'PtoVta' => $this->pointOfSale,
                    'CbteTipo' => $this->invoiceType->value,
                ],
                'FeDetReq' => [
                    'FECAEDetRequest' => [
                        'Concepto' => $this->concept->value,
                        'DocTipo' => $this->identification->type->value,
                        'DocNro' => $this->identification->number,
                        'CbteDesde' => $this->invoiceFrom,
                        'CbteHasta' => $this->invoiceTo,
                        'CbteFch' => $this->invoiceDate?->format('Ymd') ?? Carbon::now()->format('Ymd'),
                        'ImpTotal' => $this->total,
                        'ImpTotConc' => $this->nonTaxableConceptsAmount,
                        'ImpNeto' => $this->net,
                        'ImpOpEx' => $this->exempt,
                        'ImpTrib' => $this->taxes->sum('amount'),
                        'ImpIVA' => $this->vatRates->sum('amount'),
                        'FchServDesde' => $this->serviceFrom?->format('Ymd'),
                        'FchServHasta' => $this->serviceTo?->format('Ymd'),
                        'FchVtoPago' => $this->dueDate?->format('Ymd'),
                        'MonId' => $this->currency->value,
                        'MonCotiz' => $this->currencyQuote,
                        'CanMisMonExt' => $this->foreignCurrencyAmount,
                        'CondicionIVAReceptorId' => $this->vatCondition,
                        'CbtesAsoc' => $relatedInvoicesArray ? ['CbteAsoc' => $relatedInvoicesArray] : null,
                        'Tributos' => $taxArray ? ['Tributo' => $taxArray] : null,
                        'Iva' => $ivaArray ? ['AlicIva' => $ivaArray] : null,
                        'Opcionales' => $optionalsArray ? ['Opcional' => $optionalsArray] : null,
                        'Compradores' => $buyersArray ? ['Comprador' => $buyersArray] : null,
                        'PeriodoAsoc' => ($this->periodFrom || $this->periodTo) ? [
                            'FchDesde' => $this->periodFrom?->format('Ymd'),
                            'FchHasta' => $this->periodTo?->format('Ymd'),
                        ] : null,
                        'Actividades' => $this->activities->isNotEmpty()
                            ? ['Actividad' => $this->activities->map(fn($id) => ['Id' => $id])->toArray()]
                            : null,
                    ]
                ],
            ],
        ];
    }
}
