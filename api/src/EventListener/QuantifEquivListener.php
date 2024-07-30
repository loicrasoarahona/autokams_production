<?php
// src/EventListener/ProductListener.php
namespace App\EventListener;

use App\Entity\Product;
use App\Entity\QuantificationEquivalence;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Exception;

class QuantifEquivListener
{
    public function prePersist(LifecycleEventArgs $args)
    {
        $qe = $args->getObject();
        if (!$qe instanceof QuantificationEquivalence) {
            return;
        }

        // Code à exécuter avant l'insertion d'une nouvelle entité
        // Par exemple, définir une valeur par défaut pour une propriété
        $produit = $qe->getProduit();
        $quantificationProduit = $produit->getQuantification();
        $quantification = $qe->getQuantification();
        if ($quantificationProduit->getId() == $quantification->getId()) {
            // annuler l'insertion
            throw new Exception('La quantification du produit doit être différente de la quantification de l\'équivalence');
        }
    }
}
