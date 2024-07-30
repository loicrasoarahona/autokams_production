<?php

namespace App\Service;

use App\Entity\ApprovisionnementDetail;
use App\Entity\Produit;
use App\Entity\RepportANouveau;
use App\Entity\VenteDetail;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Serializer\SerializerInterface;

class ApprovisionnementService
{
    private ProduitService $produitService;
    private StockService $stockService;
    private EntityManagerInterface $em;
    private SerializerInterface $serializer;

    public function __construct(
        StockService $stockService,
        EntityManagerInterface $em,
        SerializerInterface $serializer
    ) {
        $this->stockService = $stockService;
        $this->em = $em;
        $this->serializer = $serializer;
    }

    public function setProduitService(ProduitService $produitService)
    {
        $this->produitService = $produitService;
    }

    public function estimationStock($produitId): float
    {
        $retour = 0;
        $produit = $this->em->getRepository(Produit::class)->find($produitId);
        if (empty($produit)) {
            throw new Exception("Le produit n'existe pas");
        }
        $pointDeVente = $produit->getPointDeVente();
        if (empty($pointDeVente)) {
            throw new Exception("Le produit d'a pas de point de vente");
        }
        $pointDeVenteId = $pointDeVente->getId();

        // récupérer dernier repport à nouveau
        $repport = $this->stockService->getDernierRepportProduit($produitId, $pointDeVenteId);
        $dateDebut = null;
        if (!empty($repport)) {
            $dateDebut = $repport->getDaty();
            $retour = $repport->getQuantite();
        }

        // récupérer les approvisionnements de ce produit à partir de la date (s'il y en a)
        $approvisionnementDetails = $this->getApprovisionnementDetailsProduit($produitId, $dateDebut);
        // addition
        foreach ($approvisionnementDetails as $row) {
            $retour += $row->getQuantite();
        }

        // récupérer les ventes de ce produit à partir de la date (s'il y en a)
        $venteDetails = $this->getVenteDetailsProduit($produitId, $dateDebut);
        foreach ($venteDetails as $row) {
            $retour -= $row->getQuantite();
        }

        return $retour;
    }

    public function getApprovisionnementDetailsProduit($produitId, $dateDebut)
    {
        $query = $this->em->getRepository(ApprovisionnementDetail::class)->createQueryBuilder('p')
            ->select()
            ->join('p.produit', 'produit')
            ->join('p.approvisionnement', 'approvisionnement')
            ->where('produit.id=:produitId')
            ->setParameter('produitId', $produitId)
            ->orderBy('approvisionnement.daty', 'asc');

        if (!empty($dateDebut)) {
            $query->andWhere('approvisionnement.daty>=:dateDebut');
            $query->setParameter('dateDebut', $dateDebut);
        }

        return $query->getQuery()->getResult();

        return [];
    }

    public function getVenteDetailsProduit($produitId, $dateDebut)
    {
        $query = $this->em->getRepository(VenteDetail::class)->createQueryBuilder('p')
            ->select()
            ->join('p.produit', 'produit')
            ->join('p.vente', 'vente')
            ->where('produit.id=:produitId')
            ->setParameter('produitId', $produitId)
            ->orderBy('vente.daty', 'asc');

        if (!empty($dateDebut)) {
            $query->andWhere('vente.daty>=:dateDebut');
            $query->setParameter('dateDebut', $dateDebut);
        }

        return $query->getQuery()->getResult();

        return [];
    }
    public function getQuantiteVenteProduit($produitId, $dateDebut)
    {
        $retour = 0;
        $venteDetails = $this->getVenteDetailsProduit($produitId, $dateDebut);
        foreach ($venteDetails as $row) {
            $retour += $row->getQuantite();
        }

        return $retour;
    }

    public function getQuantiteRestantePrecedente(ApprovisionnementDetail $approvisionnementDetail)
    {
        $produit = $approvisionnementDetail->getProduit();
        $dateDepart = new DateTime("1970-01-01");
        $ventes = 0;
    }
}
