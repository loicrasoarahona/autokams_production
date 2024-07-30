<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Paiement;
use App\Entity\Vente;
use App\Entity\Versement;
use Doctrine\ORM\EntityManagerInterface;

class ClientService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function getResteAPayer(Client $client)
    {
        $ventes = $client->getVentes();
        $retour = 0;
        foreach ($ventes as $vente) {
            $retour += $vente->getResteAPayer();
        }
        return $retour;
    }

    public function getVentesNonPayer(Client $client)
    {
        $results = $this->em->getRepository(Vente::class)->createQueryBuilder('vente')
            ->select()
            ->join('vente.client', 'client')
            ->where('client.id=:clientId')
            ->andWhere('vente.payed=false')
            ->setParameter('clientId', $client->getId())
            ->addOrderBy('vente.daty', 'asc')
            ->getQuery()
            ->getResult();

        return $results;
    }

    public function verserMontant(Client $client, $montant)
    {
        $ventes = $this->getVentesNonPayer($client);
        $reste = $montant;
        // creation de versement
        $versement = new Versement();
        $versement->setClient($client);
        $versement->setMontant($montant);
        $versement->setDaty(new \DateTime());
        $this->em->persist($versement);

        // dd($versement);

        // verication si le reste est inferieur ou egal a 0
        for ($i = 0; $i < count($ventes) && $reste > 0; $i++) {
            $vente = $ventes[$i];

            $resteAPayer = $vente->getResteAPayer();
            if ($resteAPayer <= 0) {
                $vente->setPayed(true);
                continue;
            }
            $montantPaiement = 0;
            if ($resteAPayer <= $reste) {
                $reste -= $resteAPayer;
                $vente->setPayed(true);
                $montantPaiement = $resteAPayer;
            } else {
                $montantPaiement = $reste;
                $reste = 0;
            }

            // creation de paiement
            $paiement = new Paiement();
            $paiement->setVente($vente);
            $paiement->setMontant($montantPaiement);
            $paiement->setDate(new \DateTime());
            $versement->addPaiement($paiement);
            $this->em->persist($paiement);
            $this->em->persist($vente);
        }

        $this->em->flush();
    }
}
