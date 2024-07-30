<?php

namespace App\Service;

use App\Entity\Paiement;
use App\Entity\PointDeVente;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class BilanVenteService
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    ) {
    }

    public function getSommePaiements(PointDeVente $pointDeVente, DateTime $dateDebut, Datetime $dateFin)
    {
        $query = $this->em->getRepository(Paiement::class)->createQueryBuilder('paiement')
            ->select('SUM(paiement.montant) as montant')
            ->join('paiement.vente', 'vente')
            ->join('vente.pointDeVente', 'pointDeVente')
            ->where('pointDeVente.id=:pointDeVenteId')
            ->andWhere('paiement.date>=:dateDebut')
            ->andWhere('paiement.date<:dateFin');

        $query->setParameters([
            'pointDeVenteId' => $pointDeVente->getId(),
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin
        ]);

        $retour = $query->getQuery()->getSingleScalarResult();

        if (!empty($retour)) {
            return $retour;
        }
        return 0;
    }

    public function getPaiements(PointDeVente $pointDeVente, DateTime $dateDebut, DateTime $dateFin, $page = 1, $limit = 30)
    {
        $query = $this->em->getRepository(Paiement::class)->createQueryBuilder('paiement')
            ->select('paiement')
            ->join('paiement.vente', 'vente')
            ->join('vente.pointDeVente', 'pointDeVente')
            ->where('pointDeVente.id=:pointDeVenteId')
            ->andWhere('paiement.date>=:dateDebut')
            ->andWhere('paiement.date<:dateFin');

        $query->addOrderBy('paiement.date', 'DESC');
        $query->setParameters([
            'pointDeVenteId' => $pointDeVente->getId(),
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin
        ]);

        $compte = count($query->getQuery()->getResult());

        $results = $query
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $normalized = $this->serializer->normalize($results, null, ["groups" => ['paiement:collection', 'vente:post']]);

        $retour = [
            "@context" => "/contexts/Paiement",
            "@id" => "/paiements/byRepport",
            "@type" => "hydra:Collection",
            "hydra:totalItems" => $compte,
            "hydra:member" => $normalized,
        ];

        return $retour;
    }
}
