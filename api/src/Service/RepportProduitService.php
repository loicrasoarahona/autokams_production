<?php

namespace App\Service;

use App\Entity\Produit;
use App\Entity\RepportNouveau;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class RepportProduitService
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function getLastRepport(Produit $produit)
    {
        $results = $this->em->getRepository(RepportNouveau::class)->createQueryBuilder('repport')
            ->select()
            ->join('repport.produit', 'produit')
            ->where('produit.id=:produitId')
            ->setParametter('produitId', $produit->getId())
            ->addOrderBy('repport.daty', 'desc')
            ->getQuery()
            ->getResult();

        if (!empty($results[0])) {
            return $results[0];
        }

        return null;
    }

    public function getRepportBefore(Produit $produit, DateTime $date)
    {
        $results = $this->em->getRepository(RepportNouveau::class)->createQueryBuilder('repport')
            ->select()
            ->join('repport.produit', 'produit')
            ->where('produit.id=:produitId')
            ->setParameter('produitId', $produit->getId())
            ->andWhere('repport.daty<:dateDebut')
            ->setParameter('dateDebut', $date)
            ->addOrderBy('repport.daty', 'desc')
            ->getQuery()
            ->getResult();

        if (!empty($results[0])) {
            return $results[0];
        }


        return null;
    }
}
