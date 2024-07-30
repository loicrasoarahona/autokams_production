<?php

namespace App\Service;

use App\Entity\Approvisionnement;
use App\Entity\ApprovisionnementDetail;
use App\Entity\DifferenceStock;
use App\Entity\Produit;
use App\Entity\VenteDetail;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ApprovisionnementDetailService
{
    private EntityManagerInterface $em;
    private ApprovisionnementService $approvisionnementService;
    private StockService $stockService;

    public function __construct(
        EntityManagerInterface $em,
        ApprovisionnementService $approvisionnementService,
        StockService $stockService,
        private LoggerInterface $logger,
        private RepportProduitService $repportProduitService,
        private SerializerInterface $serializer
    ) {
        $this->em = $em;
        $this->approvisionnementService = $approvisionnementService;
        $this->stockService = $stockService;
    }

    public function getAdExistants(Produit $produit, DateTime $date)
    {
        $dateDebut = null;
        $repport = $this->repportProduitService->getRepportBefore($produit, $date);
        if (!empty($repport)) {
            $dateDebut = $repport->getDaty();
        }


        // get all ADs
        $queryADs = $this->em->getRepository(ApprovisionnementDetail::class)->createQueryBuilder('approvisionnementDetail')
            ->select()
            ->join('approvisionnementDetail.produit', 'produit')
            ->join('approvisionnementDetail.approvisionnement', 'approvisionnement')
            ->where('produit.id=:produitId')
            ->addOrderBy('approvisionnement.daty', 'asc')
            ->setParameter('produitId', $produit->getId());

        if (!empty($dateDebut)) {
            $queryADs->andWhere('approvisionnement.daty>=:dateDebut');
            $queryADs->setParameter('dateDebut', $dateDebut);
        }
        $ADs = $queryADs->getQuery()->getResult();
        $normalizedADs = $this->serializer->normalize($ADs, null, ['groups' => ['produit:collection', 'quantification:collection', 'approvisionnementDetail:collection', 'approvisionnement:collection']]);

        // get sum difference stock
        $queryDifference = $this->em->getRepository(DifferenceStock::class)->createQueryBuilder('ds')
            ->select('SUM(ds.quantite) as quantite')
            ->join('ds.produit', 'produit')
            ->where('produit.id=:produitId')
            ->setParameter('produitId', $produit->getId())
            ->andWhere('ds.daty<:dateFin')
            ->setParameter('dateFin', $date);

        if (!empty($dateDebut)) {
            $queryDifference->andWhere('ds.daty>=:dateDebut');
            $queryDifference->setParameter('dateDebut', $dateDebut);
        }
        $difference = $queryDifference->getQuery()->getSingleScalarResult();
        $difference = -$difference;
        $differenceVD = new VenteDetail();
        $differenceVD->setQuantite($difference);

        // get all VDs before with the VD
        $queryVDs = $this->em->getRepository(VenteDetail::class)->createQueryBuilder('vd')
            ->select()
            ->join('vd.produit', 'produit')
            ->join('vd.vente', 'vente')
            ->where('produit.id=:produitId')
            ->addOrderBy('vente.daty', 'asc')
            ->setParameter('produitId', $produit->getId());

        if (!empty($dateDebut)) {
            $queryVDs->andWhere('vente.daty>=:dateDebut');
            $queryVDs->setParameter('dateDebut', $dateDebut);
        }

        $VDs = $queryVDs->getQuery()->getResult();
        array_push($VDs, $differenceVD);

        foreach ($VDs as $VDelement) {

            $quantite = $VDelement->getQuantite();
            for ($i = 0; $i < count($normalizedADs) && $quantite > 0;) {
                $AD = &$normalizedADs[0];
                if (!isset($AD['quantiteRestante']))
                    $AD['quantiteRestante'] = $AD['quantite'] + 0;
                $this->logger->info("quantite restante");
                $this->logger->info($AD['quantiteRestante']);

                if ($AD['quantiteRestante'] >= $quantite) {
                    $AD['quantiteRestante'] -= $quantite;
                    $quantite = 0;
                } else {
                    $quantite -= $AD['quantiteRestante'];
                    $AD['quantiteRestante'] = 0;
                }

                if ($AD['quantiteRestante'] == 0) {
                    array_splice($normalizedADs, 0, 1);
                    $this->logger->info("reduce the tab");
                }
            }
            $this->logger->info("Taille de AD");
            $this->logger->info(count($normalizedADs));
        }

        if (empty($normalizedADs)) {
            throw new Exception("stock insuffisant", VenteDetailService::INSUFFICIENT_STOCK);
        }

        return $normalizedADs;
    }

    public function quantiteRestante($id)
    {
        $approvisionnementDetail = $this->em->getRepository(ApprovisionnementDetail::class)->find($id);
        if (empty($approvisionnementDetail)) {
            throw new Exception("l'entité n'existe pas");
        }
        $produit = $approvisionnementDetail->getProduit();
        if (empty($produit)) {
            throw new Exception("l'entité n'a pas de produit");
        }
        $produitId = $produit->getId();
        $pointDeVente = $produit->getPointDeVente();
        if (empty($pointDeVente)) {
            throw new Exception("le produit n'a pas de point de vente");
        }
        $pointDeVenteId = $pointDeVente->getId();

        $repport = $this->stockService->getDernierRepportProduit($produitId, $pointDeVenteId);
        $dateDebut = null;
        $cumul = 0;
        if (!empty($repport)) {
            $cumul = $repport->getQuantite();
            $dateDebut = $repport->getDaty();
        }

        $nbVentes = $this->approvisionnementService->getQuantiteVenteProduit($produitId, $dateDebut);

        $listeApprovisionnementDetail = $this->approvisionnementService->getApprovisionnementDetailsProduit($produitId, $dateDebut);
        foreach ($listeApprovisionnementDetail as $row) {
            $bas = $cumul;
            $cumul += $row->getQuantite();
            if ($row->getId() == $approvisionnementDetail->getId()) {
                if ($nbVentes < $bas) {
                    return $row->getQuantite();
                }
                if ($nbVentes > $cumul) {
                    return 0;
                }

                return $cumul - $nbVentes;
            }
        }

        return 0;
    }
}
