<?php

namespace App\Repository;

use App\Entity\VenteLivraison;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VenteLivraison>
 *
 * @method VenteLivraison|null find($id, $lockMode = null, $lockVersion = null)
 * @method VenteLivraison|null findOneBy(array $criteria, array $orderBy = null)
 * @method VenteLivraison[]    findAll()
 * @method VenteLivraison[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VenteLivraisonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VenteLivraison::class);
    }

//    /**
//     * @return VenteLivraison[] Returns an array of VenteLivraison objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('v.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?VenteLivraison
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
