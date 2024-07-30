<?php

namespace App\Service;

use App\Entity\Decaissement;
use App\Entity\PointDeVente;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DecaissementService
{

    public function __construct(
        private EntityManagerInterface $em,
        private RepportCaisseService $repportCaisseService,
        private SerializerInterface $serializer
    ) {
    }

    public function getDecaissementsByRepport(PointDeVente $pointDeVente, DateTime $date = new DateTime(), $page = 1, $offset = 30, $voirTout = false)
    {
        // set time to 23:59:59
        $date->setTime(23, 59, 59);

        $dateDebut = null;
        $newDate = clone $date;
        $lastRepport = $this->repportCaisseService->findLast($pointDeVente, $newDate);
        if (!empty($lastRepport)) {
            $dateDebut = $lastRepport->getDaty();
        }


        $query = $this->em->getRepository(Decaissement::class)->createQueryBuilder('decaissement')
            ->select()
            ->join('decaissement.pointDeVente', 'pointDeVente')
            ->where('pointDeVente.id=:pointDeVenteId')
            ->andWhere('decaissement.daty<:date')
            ->setParameter('date', $date)
            ->setParameter('pointDeVenteId', $pointDeVente->getId())
            ->addOrderBy('decaissement.daty', 'DESC');

        if (!empty($dateDebut)) {
            $query->andWhere('decaissement.daty>=:dateDebut');
            $query->setParameter('dateDebut', $dateDebut);
        }

        if ($voirTout == false) {
            $debut = new DateTime($date->format('Y-m-d') . ' 00:00:00');
            $query->andWhere('decaissement.daty>=:debut');
            $query->setParameter('debut', $debut);
        }

        $compte = count($query->getQuery()->getResult());

        $results = $query
            ->setFirstResult(($page - 1) * $offset)
            ->setMaxResults($offset)
            ->getQuery()
            ->getResult();

        $normalized = $this->serializer->normalize($results);

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

    public function getTotalParJour($jour, $pointDeVente)
    {
        $connection = $this->em->getConnection();
        $lendemain = date('Y-m-d', strtotime($jour . ' + 1 day'));

        $sql = "select sum(montant) as sum from decaissement
         where daty>=:jour and daty<:lendemain and point_de_vente_id=:pointDeVenteId";

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
