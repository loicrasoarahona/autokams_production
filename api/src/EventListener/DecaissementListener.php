<?php

namespace App\EventListener;

use App\Entity\Decaissement;
use App\Entity\Decaisseur;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Decaissement::class)]
class DecaissementListener
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function prePersist(Decaissement $entite, PrePersistEventArgs $args)
    {
        $responsable = $entite->getResponsable();
        if (!empty($responsable) && !empty($responsable->getNom())) {
            // recherche
            $recherche = $this->em->getRepository(Decaisseur::class)->findByNom($responsable->getNom());
            if (!empty($recherche[0])) {
                $entite->setResponsable($recherche[0]);
            }
        }
    }
}
