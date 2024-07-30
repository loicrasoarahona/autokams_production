<?php

namespace App\EventListener;

use App\Entity\Vente;
use App\Service\StockService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

#[AsEntityListener(event: Events::preRemove, method: 'preDelete', entity: Vente::class)]
class VenteListener
{
    public function __construct(private StockService $stockService, private EntityManagerInterface $em, private SerializerInterface $serializer)
    {
    }

    public function preDelete(Vente $entite, PreRemoveEventArgs $args)
    {
        // conserver la vente
        $normalized = $this->serializer->normalize($entite, null, ['groups' => ["vente:post", "client:collection", "venteDetail:collection", "venteLivraison:collection", "produit:collection", "paiement:collection"]]);
        $id = $entite->getId();
        $dossier = "archive/ventes";
        // si le dossier n'existe pas, on le crée
        if (!is_dir($dossier)) {
            mkdir($dossier, 0777, true);
        }
        $fichier = $dossier . "/vente_" . $id . ".json";
        file_put_contents($fichier, json_encode($normalized, JSON_PRETTY_PRINT));
    }
}
