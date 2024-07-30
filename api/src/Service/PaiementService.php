<?php

namespace App\Service;

use App\Entity\Paiement;
use App\Entity\PointDeVente;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class PaiementService
{

    public function __construct(
        private EntityManagerInterface $em,
        private RepportCaisseService $repportCaisseService,
        private SerializerInterface $serializer
    ) {
    }

    public function getPaiementsByRepport(PointDeVente $pointDeVente, DateTime $date = new DateTime(), $page = 1, $offset = 30, $voirTout = false)
    {
        
        // set time to 

        $dateDebut = null;
        $lastRepport = $this->repportCaisseService->findLast($pointDeVente, $date);
        if (!empty($lastRepport)) {
            $dateDebut = $lastRepport->getDaty();
        }

        // set time to 23:59:59
        $date->setTime(23, 59, 59);

        $query = $this->em->getRepository(Paiement::class)->createQueryBuilder('paiement')
            ->select()
            ->join('paiement.vente', 'vente')
            ->join('vente.pointDeVente', 'pointDeVente')
            ->where('pointDeVente.id=:pointDeVenteId')
            ->andWhere('paiement.date<:dateFin')
            ->addOrderBy('paiement.date', 'DESC');

        $query->setParameters([
            'pointDeVenteId' => $pointDeVente->getId(),
            'dateFin' => $date
        ]);

        if (!empty($dateDebut)) {
            $query->andWhere('paiement.date>=:dateDebut');
            $query->setParameter('dateDebut', $dateDebut);
        }

        if ($voirTout == false) {
            $debut = new DateTime($date->format('Y-m-d') . ' 00:00:00');
            $query->andWhere('paiement.date>=:debut');
            $query->setParameter('debut', $debut);
        }

        $compte = count($query->getQuery()->getResult());

        $results = $query
            ->setFirstResult(($page - 1) * $offset)
            ->setMaxResults($offset)
            ->getQuery()
            ->getResult();

        $normalized = $this->serializer->normalize($results, null, ["groups" => ['paiement:collection', 'vente:post']]);

        $hydraMember = $normalized;

        $retour = [
            "@context" => "/contexts/Paiement",
            "@id" => "/paiements/byRepport",
            "@type" => "hydra:Collection",
            "hydra:totalItems" => $compte,
            "hydra:member" => $hydraMember,
        ];

        return $retour;
    }

    public function getTotalByRepport(PointDeVente $pointDeVente, DateTime $date = new DateTime())
    {
        $lastRepport = $this->repportCaisseService->findLast($pointDeVente, $date);
        $dateDebut = null;
        $quantiteInitiale = 0;
        if (!empty($lastRepport)) {
            $dateDebut = $lastRepport->getDaty();
            $quantiteInitiale += $lastRepport->getMontant();
        }

        $query = $this->em->getRepository(Paiement::class)->createQueryBuilder('paiement')
            ->select('SUM(paiement.montant) as totalMontant')
            ->join('paiement.vente', 'vente')
            ->join('vente.pointDeVente', 'pointDeVente')
            ->where('pointDeVente.id=:pointDeVenteId')
            ->andWhere('paiement.date<:dateFin')
            ->setParameter('dateFin', $date)
            ->setParameter('pointDeVenteId', $pointDeVente->getId());

        if (!empty($dateDebut)) {
            $query->andWhere('paiement.date>=:dateDebut');
            $query->setParameter('dateDebut', $dateDebut);
        }

        $result = $query->getQuery()->getResult();

        if (!empty($result[0]["totalMontant"])) {
            return $result[0]["totalMontant"] + $quantiteInitiale;
        }

        return $quantiteInitiale;
    }

    public function getTotalParJour($jour, PointDeVente $pointDeVente)
    {
        $connection = $this->em->getConnection();
        $lendemain = date('Y-m-d', strtotime($jour . ' + 1 day'));

        $sql = "select sum(montant) as sum from paiement
        join vente on vente.id=vente_id
         where paiement.date>=:jour and paiement.date<:lendemain and vente.point_de_vente_id=:pointDeVenteId";

        $stmt = $connection->prepare($sql);

        // Liez les paramètres
        $stmt->bindValue('jour', $jour);
        $stmt->bindValue('lendemain', $lendemain);
        $stmt->bindValue('pointDeVenteId', $pointDeVente->getId());

        // Exécutez la requête
        $results = $stmt->execute();

        $tableau = $results->fetchAll();
        if (!empty($tableau[0]["sum"])) {
            return $tableau[0]["sum"];
        }
        return 0;
    }
}
