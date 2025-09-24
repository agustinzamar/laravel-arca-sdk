<?php

namespace AgustinZamar\LaravelArcaSdk;

use AgustinZamar\LaravelArcaSdk\Clients\WsaaClient;
use AgustinZamar\LaravelArcaSdk\Clients\WsfeClient;
use AgustinZamar\LaravelArcaSdk\Domain\AuthorizationTicket;
use AgustinZamar\LaravelArcaSdk\Domain\Invoice;
use AgustinZamar\LaravelArcaSdk\Domain\InvoiceType;
use AgustinZamar\LaravelArcaSdk\Domain\VatCondition;
use AgustinZamar\LaravelArcaSdk\Enums\Currency;
use AgustinZamar\LaravelArcaSdk\Enums\WebService;
use Exception;
use Illuminate\Support\Collection;
use stdClass;

class ArcaService
{
    protected WsaaClient $wsaa;
    protected WsfeClient $wsfe;

    public function __construct()
    {
        $this->wsaa = new WsaaClient();
        $this->wsfe = new WsfeClient($this->wsaa);
    }

    /**
     * Obtain an authorization ticket for the specified web service.
     *
     * @param WebService|string $service
     * @return AuthorizationTicket
     */
    public function getAuthorizationTicket(WebService|string $service): AuthorizationTicket
    {
        return $this->wsaa->getAuthorizationTicket($service);
    }


    /**
     * Obtain all the recipient VAT conditions
     *
     * @return Collection<VatCondition>
     * @throws Exception
     */
    public function getRecipientVatConditions(): Collection
    {
        return $this->wsfe->getRecipientVatConditions();
    }

    /**
     * Obtain all the invoice types
     *
     * @return Collection<InvoiceType>
     * @throws Exception
     */
    public function getInvoiceTypes(): Collection
    {
        return $this->wsfe->getInvoiceTypes();
    }

    public function generateInvoice(array $params): Invoice
    {
        return $this->wsfe->generateInvoice($params);
    }
}
