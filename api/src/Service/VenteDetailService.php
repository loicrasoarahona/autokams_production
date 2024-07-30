<?php

namespace App\Service;

use App\Entity\ApprovisionnementDetail;
use App\Entity\VenteDetail;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class VenteDetailService
{
    const INSUFFICIENT_STOCK = 43;

    public function __construct(
        private RepportProduitService $repportProduitService,
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
        private LoggerInterface $logger
    ) {
    }

    public function getBenefice(VenteDetail $venteDetail, $setUpBenefice = true)
    {
        if ($setUpBenefice) {
            $retour = 0;
            $prixVente = $venteDetail->getPrix();
            $quantite = $venteDetail->getQuantite();
            $rentrant = $prixVente * $quantite;

            // stocks reliés
            $prixAchat = $this->getPrixAchat($venteDetail);

            $retour = $rentrant - $prixAchat;

            // set up the benefice
            $venteDetail->setBenefice($retour);
            $this->em->persist($venteDetail);
            $this->em->flush();

            return $retour;
        } else {
            return $venteDetail->getBenefice();
        }
    }

    public function getPrixAchat(VenteDetail $venteDetail)
    {
        $produit = $venteDetail->getProduit();
        $dateDebut = null;
        $repport = $this->repportProduitService->getRepportBefore($produit, $venteDetail->getVente()->getDaty());
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
        $normalizedADs = $this->serializer->normalize($ADs, null, ['groups' => ['approvisionnementDetail:collection', 'approvisionnement:collection']]);

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

        foreach ($VDs as $VDelement) {
            if ($VDelement->getId() == $venteDetail->getId()) {
                break;
            }

            $quantite = $VDelement->getQuantite();
            for ($i = 0; $i < count($normalizedADs) && $quantite > 0;) {
                $AD = &$normalizedADs[0];

                if ($AD['quantite'] >= $quantite) {
                    $AD['quantite'] -= $quantite;
                    $quantite = 0;
                } else {
                    $quantite -= $AD['quantite'];
                    $AD['quantite'] = 0;
                }

                if ($AD['quantite'] == 0) {
                    // pop the AD then do not increment the i
                    array_splice($normalizedADs, 0, 1);
                }
            }
            $this->logger->info("Taille de AD");
            $this->logger->info(count($normalizedADs));
        }

        if (empty($normalizedADs)) {
            throw new Exception("stock insuffisant", $this::INSUFFICIENT_STOCK);
        }

        // je compte jusqu'à épuisement
        $quantite = $venteDetail->getQuantite();
        $prixAchatTotal = 0;
        while ($quantite > 0 && !empty($normalizedADs)) {
            $AD = &$normalizedADs[0];
            if ($AD['quantite'] >= $quantite) {
                $prixAchatTotal += $quantite * $AD['prixUnit'];
                $AD['quantite'] -= $quantite;
                $quantite = 0;
            } else {
                $prixAchatTotal += $AD['quantite'] * $AD['prixUnit'];
                $quantite -= $AD['quantite'];
                $AD['quantite'] = 0;
            }

            if ($AD['quantite'] == 0) {
                // pop the AD then do not increment the i
                array_splice($normalizedADs, 0, 1);
            }
        }

        return $prixAchatTotal;
    }

    public function getStockRelie(VenteDetail $venteDetail)
    {
        $produit = $venteDetail->getProduit();
        $dateDebut = null;
        $repport = $this->repportProduitService->getRepportBefore($produit, $venteDetail->getVente()->getDaty());
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

        foreach ($VDs as $VDelement) {
            if ($VDelement->getId() == $venteDetail->getId()) {
                break;
            }

            $quantite = $VDelement->getQuantite();
            for ($i = 0; $i < count($normalizedADs) && $quantite > 0;) {
                $AD = &$normalizedADs[0];

                if ($AD['quantite'] >= $quantite) {
                    $AD['quantite'] -= $quantite;
                    $quantite = 0;
                } else {
                    $quantite -= $AD['quantite'];
                    $AD['quantite'] = 0;
                }

                if ($AD['quantite'] == 0) {
                    // pop the AD then do not increment the i
                    array_splice($normalizedADs, 0, 1);
                }
            }
            $this->logger->info("Taille de AD");
            $this->logger->info(count($normalizedADs));
        }

        if (empty($normalizedADs)) {
            throw new Exception("stock insuffisant", $this::INSUFFICIENT_STOCK);
        }

        // je compte jusqu'à épuisement
        $quantite = $venteDetail->getQuantite();
        $retour = [];
        while ($quantite > 0 && !empty($normalizedADs)) {
            $AD = &$normalizedADs[0];
            if ($AD['quantite'] >= $quantite) {
                array_push($retour, ["quantite" => $quantite, "prixAchat" => $AD['prixUnit'], "prixVente" => $venteDetail->getPrix(), "AD_id" => $AD['id'], "AD" => $AD]);
                $AD['quantite'] -= $quantite;
                $quantite = 0;
            } else {
                array_push($retour, ["quantite" => $AD['quantite'], "prixAchat" => $AD['prixUnit'], "prixVente" => $venteDetail->getPrix(), "AD_id" => $AD['id'], "AD" => $AD]);
                $quantite -= $AD['quantite'];
                $AD['quantite'] = 0;
            }

            if ($AD['quantite'] == 0) {
                // pop the AD then do not increment the i
                array_splice($normalizedADs, 0, 1);
            }
        }

        return $retour;
    }
}
