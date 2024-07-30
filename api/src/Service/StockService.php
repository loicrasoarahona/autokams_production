<?php

namespace App\Service;

use App\Entity\ApprovisionnementDetail;
use App\Entity\Produit;
use App\Entity\RepportANouveau;
use App\Entity\VenteDetail;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class StockService
{
    private EntityManagerInterface $em;

    public function __construct(
        EntityManagerInterface $em,
    ) {
        $this->em = $em;
    }

    public function getApprovisionnementDetailsExistants($produitId)
    {
        // modif : ne plus selectionner les stocks avant le dernier inventaire
        $produit = $this->em->getRepository(Produit::class)->find($produitId);
        if (empty($produit)) {
            throw new Exception("Le produit n'existe pas");
        }
        $pointDeVenteId = $produit->getPointDeVente()->getId();
        if (empty($pointDeVenteId)) {
            throw new Exception("Le produit n'a pas de point de vente");
        }
        $dateDebut = null;
        $inventaire = $this->getDernierRepportProduit($produitId, $pointDeVenteId);
        if (!empty($inventaire)) {
            $dateDebut = $inventaire->getDaty();
        }

        // selectionner toutes les approvisionnementDetails non empty utilisant ce produit
        // trié par date décroissant
        $query = $this->em->getRepository(ApprovisionnementDetail::class)->createQueryBuilder('approvisionnementDetail')
            ->select()
            ->join('approvisionnementDetail.produit', 'produit')
            ->join('approvisionnementDetail.approvisionnement', 'approvisionnement')
            ->where('produit.id=:produitId')
            ->setParameter('produitId', $produitId)
            ->andWhere('approvisionnementDetail.empty=false')
            ->orderBy('approvisionnement.daty', 'desc');

        if ($dateDebut != null) {
            $query->andWhere('approvisionnement.daty>=:dateDebut');
            $query->setParameter('dateDebut', $dateDebut);
        }

        $results = $query
            ->getQuery()
            ->getResult();

        return $results;
    }

    public function getDernierRepportProduit($produitId, $pointDeVenteId)
    {
        $results = $this->em->getRepository(RepportANouveau::class)->createQueryBuilder('repport')
            ->select()
            ->join('repport.produit', 'produit')
            ->join('repport.pointDeVente', 'pointDeVente')
            ->where('produit.id=:produitId')
            ->andWhere('pointDeVente.id=:pointDeVenteId')
            ->setParameter('produitId', $produitId)
            ->setParameter('pointDeVenteId', $pointDeVenteId)
            ->orderBy('repport.daty', 'desc')
            ->getQuery()
            ->getResult();
        if (!empty($results[0])) {
            return $results[0];
        }
        return null;
    }

    // public function quantiteRestanteProduit($produitId)
    // {
    //     $produit = $this->em->getRepository(Produit::class)->find($produitId);
    //     if (empty($produit)) {
    //         throw new Exception("Le produit n'existe pas");
    //     }
    //     $pointDeVenteId = 0;
    //     $pointDeVenteId = $produit->getPointDeVente()->getId();
    //     if ($pointDeVenteId == 0) {
    //         throw new Exception("Le produit n'a pas de point de vente");
    //     }
    //     $quantiteParDefaut = 0;
    //     $dateDebut = null;
    //     $dateFin = null;
    //     // selction inventaire
    //     $inventaire = $this->getDernierRepportProduit($produitId, $pointDeVenteId);
    //     if (!empty($inventaire)) {
    //         $quantiteParDefaut = $inventaire->getQuantite();
    //         $dateDebut = $inventaire->getDaty();
    //     }

    //     // selection quantite stocks details
    //     $quantiteApprovisionnements = $this->produitService->getQuantiteApprovisionnement($produit, $dateDebut, $dateFin, $pointDeVenteId);
    //     $quantiteParDefaut += $quantiteApprovisionnements;

    //     // selection vente details
    //     $quantiteVentes = $this->produitService->getQuantiteVente($produit, $dateDebut, $dateFin, $pointDeVenteId);
    //     $quantiteParDefaut -= $quantiteVentes;

    //     return $quantiteParDefaut;
    // }

    public function getStockEnCours($produitId)
    {
        // selectionner toutes les approvisionnementDetails non empty utilisant ce produit
        // trié par date croissant
        $results = $this->em->getRepository(ApprovisionnementDetail::class)->createQueryBuilder('approvisionnementDetail')
            ->select()
            ->join('approvisionnementDetail.produit', 'produit')
            ->join('approvisionnementDetail.approvisionnement', 'approvisionnement')
            ->where('produit.id=:produitId')
            ->setParameter('produitId', $produitId)
            ->andWhere('approvisionnementDetail.empty=false')
            ->orderBy('approvisionnement.daty', 'asc')
            ->getQuery()
            ->getResult();
        // et prendre le plus recent
        if (!empty($results[0])) {
            return $results[0];
        }
        // retourner vide s'il n'y en a pas
        return null;
    }


    public function quantiteRestanteApprovisionnementDetail($approvisionnementDetailId)
    {
        // selection de l'approvisionnementDetail
        $approvisionnementDetail = $this->em->getRepository(ApprovisionnementDetail::class)->find($approvisionnementDetailId);
        if (empty($approvisionnementDetail)) {
            throw new Exception("ApprovisionnementDetail inexistant");
        }
        // le produit
        $produit = $approvisionnementDetail->getProduit();
        if (empty($produit)) {
            throw new Exception("Le stock n'a pas de produit en liaison");
        }

        // la quantification par défaut
        $quantificationDefaut = $produit->getQuantification();
        if (empty($quantificationDefaut)) {
            throw new Exception("Le produit n'a pas de quantification");
        }

        // les quantifications equivalences
        $quantificationEquivalences = $produit->getQuantificationEquivalences();

        // la quantité du stock
        // vérification des quantification et conversion si besoin
        $quantiteStock = $approvisionnementDetail->getQuantite();
        $quantificationStock = $approvisionnementDetail->getQuantification();
        if (empty($quantificationStock)) {
            throw new Exception("Le stock n'a pas de quantification");
        }
        if (strcasecmp($quantificationDefaut->getSymbole(), $quantificationStock->getSymbole()) != 0) {
            // la quantification du stock et du produit ne sont pas les mêmes, convertir
            $equivalence = null;
            foreach ($quantificationEquivalences as $element) {
                if ($element->getQuantification()->getId() == $quantificationStock->getId()) {
                    $equivalence = $element;
                    break;
                }
            }
            if ($equivalence == null) {
                throw new Exception("Le stock est enregistré avec une autre quantification (" . $quantificationStock->getSymbole() . ") qui n'est pas enregistré dans le produit");
            }
            // conversion
            $quantiteStock /= $equivalence->getValeur();
        }


        // selectionner toutes les ventes qui ont utilisé ce stock
        $results = $this->em->getRepository(VenteDetail::class)->createQueryBuilder('venteDetail')
            ->select()
            ->join('venteDetail.approvisionnementDetail', 'approvisionnementDetail')
            ->where('approvisionnementDetail.id=:approvisionnementDetailId')
            ->setParameter('approvisionnementDetailId', $approvisionnementDetailId)
            ->getQuery()
            ->getResult();

        $quantiteVentes = 0;
        foreach ($results as $venteDetail) {
            $quantiteDetail = $venteDetail->getQuantite();
            // convertir les quantités si possible
            // verification de la quantification
            $quantificationVente = $venteDetail->getUnite();
            if (strcasecmp($quantificationVente->getSymbole(), $quantificationDefaut->getSymbole()) != 0) {
                // mila conversion, raha tsy mety dee dinganiko :(
                $equivalence = null;
                foreach ($quantificationEquivalences as $element) {
                    if ($element->getQuantification()->getId() == $quantificationVente->getId()) {
                        $equivalence = $element;
                        break;
                    }
                }
                if ($equivalence == null) {
                    // equivalence inexistant
                    // notifier l'utilisateur
                    continue;
                }
                $quantiteDetail /= $equivalence->getValeur();
            }
            // additionner les quantités
            $quantiteVentes += $quantiteDetail;
        }

        // soustraire de la quantité du stock
        $retour = $quantiteStock - $quantiteVentes;

        if ($retour <= 0) {
            // il faut mettre à jour le stock en empty
        }
        return $retour;
    }
}
