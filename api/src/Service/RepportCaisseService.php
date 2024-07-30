<?php

namespace App\Service;

use App\Entity\Decaissement;
use App\Entity\Paiement;
use App\Entity\PointDeVente;
use App\Entity\RepportCaisse;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class RepportCaisseService
{

    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function montantEnCaisse(PointDeVente $pointDeVente, $date = new DateTime())
    {
        // set time to 23:59:59
        $date = $date->setTime(23, 59, 59);

        $dateDebut = null;
        $newDate = clone $date;
        $lastRepport = $this->findLast($pointDeVente, $newDate);
        if (!empty($lastRepport)) {
            $dateDebut = $lastRepport->getDaty();
        }

        $queryTotalPaiement = $this->em->getRepository(Paiement::class)->createQueryBuilder('paiement')
            ->select('SUM(paiement.montant) as total')
            ->join('paiement.vente', 'vente')
            ->join('vente.pointDeVente', 'pointDeVente')
            ->where('pointDeVente.id=:pointDeVenteId')
            ->andWhere('paiement.date<=:date')
            ->setParameter('date', $date)
            ->setParameter('pointDeVenteId', $pointDeVente->getId());

        if (!empty($dateDebut)) {
            $queryTotalPaiement->andWhere('paiement.date>=:dateDebut')
                ->setParameter('dateDebut', $dateDebut);
        }

        $totalPaiement = $queryTotalPaiement->getQuery()->getSingleScalarResult();


        $queryTotalDecaissement = $this->em->getRepository(Decaissement::class)->createQueryBuilder('decaissement')
            ->select('SUM(decaissement.montant) as total')
            ->join('decaissement.pointDeVente', 'pointDeVente')
            ->where('pointDeVente.id=:pointDeVenteId')
            ->andWhere('decaissement.daty<=:date')
            ->setParameter('date', $date)
            ->setParameter('pointDeVenteId', $pointDeVente->getId());


        if (!empty($dateDebut)) {
            $queryTotalDecaissement->andWhere('decaissement.daty>=:dateDebut')
                ->setParameter('dateDebut', $dateDebut);
        }

        $totalDecaissement = $queryTotalDecaissement->getQuery()->getSingleScalarResult();

        return $totalPaiement - $totalDecaissement + $lastRepport->getMontant();
    }

    public function findLast(PointDeVente $pointDeVente, $date = new DateTime())
    {
        // set time to 00:00:00
        $date->setTime(0, 0, 0);

        $result = $this->em->getRepository(RepportCaisse::class)->createQueryBuilder('repport')
            ->select()
            ->join('repport.pointDeVente', 'pointDeVente')
            ->where('pointDeVente.id=:pointDeVenteId')
            ->andWhere('repport.daty<:date')
            ->setParameter('date', $date)
            ->setParameter('pointDeVenteId', $pointDeVente->getId())
            ->addOrderBy('repport.daty', 'desc')
            ->getQuery()
            ->getResult();

        if (!empty($result[0])) {
            return $result[0];
        }

        return null;
    }
}
