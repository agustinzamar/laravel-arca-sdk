<?php

namespace AgustinZamar\LaravelArcaSdk\Contracts\Request;

use AgustinZamar\LaravelArcaSdk\Domain\Identification;
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
        public readonly ?Carbon        $invoiceDate = null,
        public readonly ?Collection    $taxes = new Collection,
        public readonly ?Collection    $vatRates = new Collection,
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
            invoiceDate: $this->invoiceDate,
            taxes: $this->taxes,
            vatRates: $this->vatRates,
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
                        'CondicionIVAReceptorId' => $this->vatCondition,
                        'Tributos' => $taxArray ? ['Tributo' => $taxArray] : null,
                        'Iva' => $ivaArray ? ['AlicIva' => $ivaArray] : null,
                    ],
                ],
            ],
        ];
    }
}
