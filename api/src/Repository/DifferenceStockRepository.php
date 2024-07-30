<?php

namespace App\Repository;

use App\Entity\DifferenceStock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DifferenceStock>
 *
 * @method DifferenceStock|null find($id, $lockMode = null, $lockVersion = null)
 * @method DifferenceStock|null findOneBy(array $criteria, array $orderBy = null)
 * @method DifferenceStock[]    findAll()
 * @method DifferenceStock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DifferenceStockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DifferenceStock::class);
    }

//    /**
//     * @return DifferenceStock[] Returns an array of DifferenceStock objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DifferenceStock
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
