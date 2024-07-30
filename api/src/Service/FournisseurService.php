<?php

namespace App\Service;

use App\Entity\ApprovisionnementDetail;
use App\Entity\Fournisseur;
use App\Entity\PaiementStock;
use Doctrine\ORM\EntityManagerInterface;

class FournisseurService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function getMontantTotal(Fournisseur $fournisseur)
    {
        $retour = $this->em->getRepository(ApprovisionnementDetail::class)->createQueryBuilder('ad')
            ->select('SUM(ad.prixUnit * ad.quantite) as total')
            ->join('ad.approvisionnement', 'approvisionnement')
            ->join('approvisionnement.fournisseur', 'fournisseur')
            ->where('fournisseur.id=:fournisseurId')
            ->setParameter('fournisseurId', $fournisseur->getId())
            ->getQuery()
            ->getSingleScalarResult();

        if (!empty($retour)) {
            return $retour;
        }
        return 0;
    }

    public function getTotalPaiements(Fournisseur $fournisseur)
    {
        $retour = $this->em->getRepository(PaiementStock::class)->createQueryBuilder('paie')
            ->select('SUM(paie.montant) as total')
            ->join('paie.approvisionnement', 'approvisionnement')
            ->join('approvisionnement.fournisseur', 'fournisseur')
            ->where('fournisseur.id=:fournisseurId')
            ->setParameter('fournisseurId', $fournisseur->getId())
            ->getQuery()
            ->getSingleScalarResult();

        if (!empty($retour)) {
            return $retour;
        }
        return 0;
    }
    public function getRecouvrementInfos(Fournisseur $fournisseur)
    {
        $montantTotal = $this->getMontantTotal($fournisseur);
        $totalPaiements = $this->getTotalPaiements($fournisseur);
        $restePayer = $montantTotal - $totalPaiements;

        return ["montantTotal" => $montantTotal, "totalPaiements" => $totalPaiements, "resteAPayer" => $restePayer];
    }
}
