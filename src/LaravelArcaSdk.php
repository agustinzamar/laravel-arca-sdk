<?php

namespace AgustinZamar\LaravelArcaSdk;

use AgustinZamar\LaravelArcaSdk\Clients\WsaaClient;
use AgustinZamar\LaravelArcaSdk\Clients\WsfeClient;
use AgustinZamar\LaravelArcaSdk\Contracts\Request\CreateInvoiceRequest;
use AgustinZamar\LaravelArcaSdk\Contracts\Response\InvoiceCreatedResponse;
use AgustinZamar\LaravelArcaSdk\Contracts\Response\InvoiceDetailResponse;
use AgustinZamar\LaravelArcaSdk\Contracts\Response\InvoiceTypeResponse;
use AgustinZamar\LaravelArcaSdk\Contracts\Response\OptionalTypesResponse;
use AgustinZamar\LaravelArcaSdk\Contracts\Response\VatConditionResponse;
use AgustinZamar\LaravelArcaSdk\Domain\AuthorizationTicket;
use AgustinZamar\LaravelArcaSdk\Enums\InvoiceType;
use AgustinZamar\LaravelArcaSdk\Enums\WebService;
use AgustinZamar\LaravelArcaSdk\Exceptions\ArcaException;
use AgustinZamar\LaravelArcaSdk\Support\ArcaErrors;
use Illuminate\Support\Collection;
use stdClass;

class LaravelArcaSdk
{
    protected WsaaClient $wsaa;

    protected WsfeClient $wsfe;

    public function __construct()
    {
        $this->wsaa = new WsaaClient;
        $this->wsfe = new WsfeClient($this->wsaa);
    }

    /**
     * Obtain an authorization ticket for the specified web service.
     */
    public function getAuthorizationTicket(WebService|string $service): AuthorizationTicket
    {
        return $this->wsaa->getAuthorizationTicket($service);
    }

    /**
     * Obtain all the points of sale which are enabled for Web Services usage
     *
     * @throws ArcaException
     */
    public function getPointsOfSale(): stdClass|ArcaErrors
    {
        return $this->wsfe->getPointsOfSale();
    }

    /**
     * Obtain all the recipient VAT conditions
     *
     * @return Collection<VatConditionResponse>|ArcaErrors
     *
     * @throws ArcaException
     */
    public function getRecipientVatConditions(): Collection|ArcaErrors
    {
        return $this->wsfe->getRecipientVatConditions();
    }

    /**
     * Generate an invoice with the provided parameters
     *
     *
     * @throws ArcaException
     */
    public function generateInvoice(CreateInvoiceRequest $request): InvoiceCreatedResponse|ArcaErrors
    {
        return $this->wsfe->generateInvoice($request);
    }

    /**
     * Obtain the last invoice number for the specified point of sale and invoice type
     *
     * @throws ArcaException
     */
    public function getLastInvoiceNumber(int $pointOfSale, InvoiceType|int $invoiceType): int|ArcaErrors
    {
        return $this->wsfe->getLastInvoiceNumber($pointOfSale, $invoiceType);
    }

    /**
     * Generate the next invoice based on the provided parameters
     *
     * @throws ArcaException
     */
    public function generateNextInvoice(CreateInvoiceRequest $request): InvoiceCreatedResponse|ArcaErrors
    {
        return $this->wsfe->generateNextInvoice($request);
    }

    /**
     * Retrieve the details of a specific invoice
     *
     * @return InvoiceDetailResponse
     *
     * @throws ArcaException
     */
    public function getInvoiceDetails(int $pointOfSale, InvoiceType|int $invoiceType, int $invoiceNumber): InvoiceDetailResponse|ArcaErrors
    {
        return $this->wsfe->getInvoiceDetails($pointOfSale, $invoiceType, $invoiceNumber);
    }

    /**
     * Retrieve all allowed optional types
     *
     * @return Collection<OptionalTypesResponse>
     *
     * @throws ArcaException
     */
    public function getOptionalTypes(): Collection|ArcaErrors
    {
        return $this->wsfe->getOptionalTypes();
    }

    /**
     * Retrieve all invoice types
     *
     * @return Collection<InvoiceTypeResponse>
     *
     * @throws ArcaException
     */
    public function getInvoiceTypes(): Collection|ArcaErrors
    {
        return $this->wsfe->getInvoiceTypes();
    }
}
