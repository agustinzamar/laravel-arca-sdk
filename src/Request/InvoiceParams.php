<?php

namespace AgustinZamar\LaravelArcaSdk\Request;

use AgustinZamar\LaravelArcaSdk\Domain\Identification;
use AgustinZamar\LaravelArcaSdk\Domain\Tax;
use AgustinZamar\LaravelArcaSdk\Domain\Vat;
use AgustinZamar\LaravelArcaSdk\Enums\Currency;
use AgustinZamar\LaravelArcaSdk\Enums\InvoiceConcept;
use AgustinZamar\LaravelArcaSdk\Enums\InvoiceType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class InvoiceParams
{
    private InvoiceConcept $concept;
    private int $pointOfSale;
    private Identification $identification;
    private InvoiceType $invoiceType;
    private int $invoiceFrom;
    private int $invoiceTo;
    private Carbon $invoiceDate;
    private float $total;
    private float $net;
    private float $exempt = 0;
    private ?Carbon $serviceFrom = null;
    private ?Carbon $serviceTo = null;
    private ?Carbon $dueDate = null;
    private Currency $currency = Currency::ARS;
    private float $currencyQuote = 1;
    private int $vatCondition;

    /** @var Collection<Tax> */
    private Collection $taxes;

    /** @var Collection<Vat> */
    private Collection $vatRates;

    public function __construct()
    {
        $this->taxes = collect();
        $this->vatRates = collect();
        $this->invoiceDate = now();
    }

    /* ---------- [ Setters ] ----------  */
    public function setConcept(InvoiceConcept $concept): self
    {
        $this->concept = $concept;
        return $this;
    }

    public function setPointOfSale(int $pointOfSale): self
    {
        $this->pointOfSale = $pointOfSale;
        return $this;
    }

    public function setIdentification(Identification $identification): self
    {
        $this->identification = $identification;
        return $this;
    }

    public function setInvoiceType(InvoiceType $invoiceType): self
    {
        $this->invoiceType = $invoiceType;
        return $this;
    }

    public function setInvoiceFrom(int $invoiceFrom): self
    {
        $this->invoiceFrom = $invoiceFrom;
        return $this;
    }

    public function setInvoiceTo(int $invoiceTo): self
    {
        $this->invoiceTo = $invoiceTo;
        return $this;
    }

    public function setInvoiceRange(int $from, int $to): self
    {
        $this->invoiceFrom = $from;
        $this->invoiceTo = $to;
        return $this;
    }

    public function setInvoiceDate(Carbon|string $date): self
    {
        $this->invoiceDate = $date instanceof Carbon ? $date : Carbon::parse($date);
        return $this;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;
        return $this;
    }

    public function setNet(float $net): self
    {
        $this->net = $net;
        return $this;
    }

    public function setExempt(float $exempt): self
    {
        $this->exempt = $exempt;
        return $this;
    }

    public function setCurrency(Currency $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function setCurrencyQuote(float $currencyQuote): InvoiceParams
    {
        $this->currencyQuote = $currencyQuote;
        return $this;
    }

    public function setVatCondition(int $condition): self
    {
        $this->vatCondition = $condition;
        return $this;
    }

    public function setServicePeriod(?Carbon $from, ?Carbon $to): self
    {
        $this->serviceFrom = $from ?? null;
        $this->serviceTo = $to ?? null;
        return $this;
    }

    public function setDueDate(?Carbon $dueDate): self
    {
        $this->dueDate = $dueDate ?? null;
        return $this;
    }

    /* ---------- [ Collection Helpers ] ----------  */

    public function addTax(Tax $tax): self
    {
        $this->taxes->push($tax);
        return $this;
    }

    public function addVat(Vat $vat): self
    {
        if ($this->invoiceType === InvoiceType::FACTURA_C && $this->vatRates->sum('amount') > 0) {
            throw new \InvalidArgumentException("Factura C no puede tener IVA.");
        }

        $this->vatRates->push($vat);
        return $this;
    }

    /* ---------- [ Getters ] ----------  */

    public function getConcept(): InvoiceConcept
    {
        return $this->concept;
    }

    public function getPointOfSale(): int
    {
        return $this->pointOfSale;
    }

    public function getIdentification(): Identification
    {
        return $this->identification;
    }

    public function getInvoiceType(): InvoiceType
    {
        return $this->invoiceType;
    }

    public function getInvoiceFrom(): int
    {
        return $this->invoiceFrom;
    }

    public function getInvoiceTo(): int
    {
        return $this->invoiceTo;
    }

    public function getInvoiceDate(): Carbon
    {
        return $this->invoiceDate;
    }

    public function getTotal(): float
    {
        return $this->total;
    }

    public function getNet(): float
    {
        return $this->net;
    }

    public function getExempt(): float
    {
        return $this->exempt;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function getCurrencyQuote(): float
    {
        return $this->currencyQuote;
    }

    public function getVatCondition(): int
    {
        return $this->vatCondition;
    }

    public function getServiceFrom(): Carbon
    {
        return $this->serviceFrom;
    }

    public function getServiceTo(): Carbon
    {
        return $this->serviceTo;
    }

    public function getDueDate(): Carbon
    {
        return $this->dueDate;
    }

    public function getTaxes(): Collection
    {
        return $this->taxes;
    }

    public function getVatRates(): Collection
    {
        return $this->vatRates;
    }

    /**
     * Genera el array que va al WebService de ARCA
     */
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
                    'CbteTipo' => $this->invoiceType,
                ],
                'FeDetReq' => [
                    'FECAEDetRequest' => [
                        'Concepto' => $this->concept->value,
                        'DocTipo' => $this->identification->type->value,
                        'DocNro' => $this->identification->number,
                        'CbteDesde' => $this->invoiceFrom,
                        'CbteHasta' => $this->invoiceTo,
                        'CbteFch' => $this->invoiceDate->format('Ymd'),
                        'ImpTotal' => $this->total,
                        'ImpTotConc' => 0,
                        'ImpNeto' => $this->net,
                        'ImpOpEx' => $this->exempt,
                        'ImpTrib' => $this->taxes->sum('amount'),
                        'ImpIVA' => $this->vatRates->sum('amount'),
                        'FchServDesde' => $this->serviceFrom ? $this->serviceFrom->format('Ymd') : null,
                        'FchServHasta' => $this->serviceTo ? $this->serviceTo->format('Ymd') : null,
                        'FchVtoPago' => $this->dueDate ? $this->dueDate->format('Ymd') : null,
                        'MonId' => $this->currency->value,
                        'MonCotiz' => $this->currencyQuote,
                        'CondicionIVAReceptorId' => $this->vatCondition,
                        'Tributos' => !empty($taxArray) ? ['Tributo' => $taxArray] : null,
                        'Iva' => !empty($ivaArray) ? ['AlicIva' => $ivaArray] : null,
                    ],
                ],
            ],
        ];
    }
}
