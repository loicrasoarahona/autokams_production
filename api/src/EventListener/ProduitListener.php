<?php

namespace App\EventListener;

use ApiPlatform\Symfony\EventListener\EventPriorities as EventListenerEventPriorities;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Entity\Product;
use App\Entity\Produit;
use App\Entity\ProduitPrix;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ProduitListener implements EventSubscriberInterface
{

    public function __construct(private EntityManagerInterface $em, private LoggerInterface $logger)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventListenerEventPriorities::PRE_WRITE],
        ];
    }

    public function onKernelView(ViewEvent $event)
    {
        $request = $event->getRequest();

        if (!in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT])) {
            return;
        }

        $product = $event->getControllerResult();

        if (!$product instanceof Produit) {
            return;
        }

        $this->logger->info('ProductListener called');
        // Ajoutez ici votre logique de validation ou autre traitement
        $this->savePrix($product);
    }

    private function savePrix(Produit $produit)
    {
        $prix = $produit->getPrix();
        if (!empty($prix)) {

            $produitPrix = new ProduitPrix();
            $produitPrix->setProduit($produit);
            $produitPrix->setPrix($prix);
            $produitPrix->setDate(new DateTime());

            $quantification = $produit->getQuantification();
            if (!empty($quantification->getSymbole())) {
                $produitPrix->setQuantification($quantification->getSymbole());
            }
            $this->em->persist($produitPrix);
        }
    }
}
