<?php

namespace App\EventListener;

use App\Entity\Paiement;
use App\Service\VenteService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;


#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Paiement::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postDelete', entity: Paiement::class)]
class PaiementListener
{
    public function __construct(private EntityManagerInterface $em, private  VenteService $venteService)
    {
    }

    public function postPersist(Paiement $paiement, PostPersistEventArgs $args)
    {
        $vente = $paiement->getVente();
        $resteAPayer = $this->venteService->resteAPayer($vente);
        if ($resteAPayer <= 0) {
            $vente->setPayed(true);
            $this->em->persist($vente);
            $this->em->flush();
        }
    }

    public function postDelete(Paiement $paiement)
    {
        $vente = $paiement->getVente();
        $resteAPayer = $this->venteService->resteAPayer($vente);
        if ($resteAPayer > 0) {
            $vente->setPayed(false);
            $this->em->persist($vente);
            $this->em->flush();
        }
    }
}
