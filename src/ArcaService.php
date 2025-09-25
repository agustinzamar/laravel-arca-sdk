<?php

namespace AgustinZamar\LaravelArcaSdk;

use AgustinZamar\LaravelArcaSdk\Clients\WsaaClient;
use AgustinZamar\LaravelArcaSdk\Clients\WsfeClient;
use AgustinZamar\LaravelArcaSdk\Domain\AuthorizationTicket;
use AgustinZamar\LaravelArcaSdk\Domain\Invoice;
use AgustinZamar\LaravelArcaSdk\Domain\VatCondition;
use AgustinZamar\LaravelArcaSdk\Enums\InvoiceType;
use AgustinZamar\LaravelArcaSdk\Enums\WebService;
use AgustinZamar\LaravelArcaSdk\Request\InvoiceParams;
use Exception;
use Illuminate\Support\Collection;
use stdClass;

class ArcaService
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
     * @throws Exception
     */
    public function getPointsOfSale(): stdClass
    {
        return $this->wsfe->getPointsOfSale();
    }

    /**
     * Obtain all the recipient VAT conditions
     *
     * @return Collection<VatCondition>
     *
     * @throws Exception
     */
    public function getRecipientVatConditions(): Collection
    {
        return $this->wsfe->getRecipientVatConditions();
    }

    /**
     * Generate an invoice with the provided parameters
     *
     * @param  array  $params
     *
     * @throws Exception
     */
    public function generateInvoice(InvoiceParams $params): Invoice
    {
        return $this->wsfe->generateInvoice($params);
    }

    /**
     * Obtain the last invoice number for the specified point of sale and invoice type
     *
     * @throws Exception
     */
    public function getLastInvoiceNumber(int $pointOfSale, InvoiceType|int $invoiceType): int
    {
        return $this->wsfe->getLastInvoiceNumber($pointOfSale, $invoiceType);
    }

    /**
     * Generate the next invoice based on the provided parameters
     *
     * @throws Exception
     */
    public function generateNextInvoice(InvoiceParams $params): Invoice
    {
        return $this->wsfe->generateNextInvoice($params);
    }
}
