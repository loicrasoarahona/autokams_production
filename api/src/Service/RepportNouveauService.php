<?php

namespace App\Service;

use App\Entity\Produit;
use App\Entity\RepportANouveau;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class RepportNouveauService
{
    public function __construct(
        private StockService $stockService,
        private EntityManagerInterface $em,
        private ApprovisionnementService $approvisionnementService
    ) {
    }

    public function quantiteRestante($id)
    {
        $repport = $this->em->getRepository(RepportANouveau::class)->find($id);
        if (!$repport) {
            throw new Exception("Le repport n'existe pas");
        }
        $produit = $repport->getProduit();
        if (!$produit) {
            throw new Exception("Produit indéfini");
        }
        $pointDeVente = $produit->getPointDeVente();
        if (!$pointDeVente) {
            throw new Exception("Point de vente indéfini");
        }
        // selectionner le dernier repport
        $dernierRepport = $this->stockService->getDernierRepportProduit($produit->getId(), $pointDeVente->getId());
        // si c'est lui, ...
        if ($dernierRepport->getId() == $repport->getId()) {
            $dateDebut = $dernierRepport->getDaty();

            $quantiteVente = $this->approvisionnementService->getQuantiteVenteProduit($produit->getId(), $dateDebut);
            $retour = $dernierRepport->getQuantite() - $quantiteVente;
            if ($retour < 0) {
                return 0;
            } else {
                return $retour;
            }
        }
        // sinon, retourner 0
        else {
            return 0;
        }
    }

    public function quantiteRestanteParProduit($produitId)
    {
        $produit = $this->em->getRepository(Produit::class)->find($produitId);
        if (empty($produit)) {
            throw new Exception("Le produit n'existe pas");
        }
        $pointDeVente = $produit->getPointDeVente();
        if (empty($pointDeVente)) {
            throw new Exception("Le produit n'a pas de point de vente");
        }

        $currentRepport = $this->stockService->getDernierRepportProduit($produitId, $pointDeVente->getId());
        if (empty($currentRepport)) {
            throw new Exception("Aucun repport existant");
        }
        $dateDebut = $currentRepport->getDaty();
        $quantite = 0;
        $quantite = $currentRepport->getQuantite();

        $nbVentes = $this->approvisionnementService->getQuantiteVenteProduit($produitId, $dateDebut);
        $quantite -= $nbVentes;

        if ($quantite <= 0) {
            return 0;
        }
        return $quantite;
    }
}
