<?php

namespace App\Service;

use App\Entity\Charge;
use App\Entity\PointDeVente;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class ChargeService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function getSommeCharge(PointDeVente $pointDeVente, DateTime $dateDebut, DateTime $dateFin)
    {
        $result = $this->em->getRepository(Charge::class)->createQueryBuilder('charge')
            ->select('SUM(charge.montant)')
            ->join('charge.pointDeVente', 'pointDeVente')
            ->where('pointDeVente.id = :pointDeVenteId')
            ->andWhere('charge.daty>=:dateDebut')
            ->andWhere('charge.daty<:dateFin')
            ->setParameters([
                'pointDeVenteId' => $pointDeVente->getId(),
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin
            ])
            ->getQuery()
            ->getSingleScalarResult();

        if (empty($result)) {
            return 0;
        }
        return $result;
    }

    public function getChargesWithDecaissements(PointDeVente $pointDeVente, DateTime $dateDebut, DateTime $dateFin)
    {
        $dateDebutStr = $dateDebut->format('Y-m-d H:i:s');
        $dateFinStr = $dateFin->format('Y-m-d H:i:s');
        $sql = "select * from
        (select decaissement.point_de_vente_id, decaissement.montant, decaissement.description as intitule, decaissement.daty as daty, 'Décaissement' as type
        from decaissement
        union
        select charge.point_de_vente_id, charge.montant, charge.intitule as intitule, charge.daty as daty, 'Charge' as type
        from charge) 
        as combined_results
        where point_de_vente_id = " . $pointDeVente->getId() . "
        and daty >= '" . $dateDebutStr . "' and daty < '" . $dateFinStr . "'
        order by daty desc";

        $result = $this->em->getConnection()->executeQuery($sql)->fetchAllAssociative();

        $sql = "select SUM(montant) from
        (select decaissement.point_de_vente_id, decaissement.montant, decaissement.description as intitule, decaissement.daty as daty
        from decaissement
        union
        select charge.point_de_vente_id, charge.montant, charge.intitule as intitule, charge.daty as daty
        from charge) 
        as combined_results
        where point_de_vente_id = " . $pointDeVente->getId() . "
        and daty >= '" . $dateDebutStr . "' and daty < '" . $dateFinStr . "'
        order by daty desc";

        $somme = $this->em->getConnection()->executeQuery($sql)->fetchOne();

        $retour = [
            'somme' => $somme,
            'chargesWithDecaissements' => $result
        ];

        return $retour;
    }
}
