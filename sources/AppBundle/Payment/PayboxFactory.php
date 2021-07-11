<?php


namespace AppBundle\Payment;

use AppBundle\Event\Model\Event;
use AppBundle\Event\Model\Invoice;
use Symfony\Component\Routing\RouterInterface;

class PayboxFactory
{
    /**
     * @var RouterInterface
     */
    private $router;

    private $payboxDomainServer;
    private $payboxSecretKey;
    private $payboxSite;
    private $payboxRang;
    private $payboxIdentifiant;

    public function __construct(RouterInterface $router, $payboxDomainServer, $payboxSecretKey, $payboxSite, $payboxRang, $payboxIdentifiant)
    {
        $this->router = $router;
        $this->payboxDomainServer = $payboxDomainServer;
        $this->payboxSecretKey = $payboxSecretKey;
        $this->payboxSite = $payboxSite;
        $this->payboxRang = $payboxRang;
        $this->payboxIdentifiant = $payboxIdentifiant;
    }

    /**
     * @param string $facture Facture id
     * @param float $montant Amount to pay
     * @param string $email Email of the company
     *
     * @return string html with payment button
     */
    public function createPayboxForSubscription($facture, $montant, $email)
    {
        $paybox = $this->getPaybox();

        $now = new \DateTime();

        $returnUrl = $this->router->generate('membership_payment_redirect', [], RouterInterface::ABSOLUTE_URL);
        $ipnUrl = $this->router->generate('membership_payment', [], RouterInterface::ABSOLUTE_URL);

        $paybox
            ->setTotal($montant * 100) // Total de la commande, en centimes d'euros
            ->setCmd($facture) // Référence de la commande
            ->setPorteur($email) // Email du client final (Le porteur de la carte)
            ->setUrlRetourEffectue($returnUrl)
            ->setUrlRetourRefuse($returnUrl)
            ->setUrlRetourAnnule($returnUrl)
            ->setUrlRepondreA($ipnUrl)
        ;


        return $paybox->generate($now);
    }

    public function createPayboxForTicket(Invoice $invoice, Event $event)
    {
        $paybox = $this->getPaybox();

        $now = new \DateTime();

        $returnUrl = $this->router->generate('ticket_paybox_redirect', ['eventSlug' => $event->getPath()], RouterInterface::ABSOLUTE_URL);
        $ipnUrl = $this->router->generate('ticket_paybox_callback', ['eventSlug' => $event->getPath()], RouterInterface::ABSOLUTE_URL);

        $paybox
            ->setTotal($invoice->getAmount() * 100) // Total de la commande, en centimes d'euros
            ->setCmd($invoice->getReference()) // Référence de la commande
            ->setPorteur($invoice->getEmail()) // Email du client final (Le porteur de la carte)
            ->setUrlRetourEffectue($returnUrl)
            ->setUrlRetourRefuse($returnUrl)
            ->setUrlRetourAnnule($returnUrl)
            ->setUrlRepondreA($ipnUrl)
        ;

        return $paybox->generate($now);
    }

    public function getPaybox()
    {
        return new Paybox(
            $this->payboxDomainServer,
            $this->payboxSecretKey,
            $this->payboxSite,
            $this->payboxRang,
            $this->payboxIdentifiant
        );
    }
}
