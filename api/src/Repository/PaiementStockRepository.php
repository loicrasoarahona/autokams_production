<?php

namespace App\Repository;

use App\Entity\PaiementStock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaiementStock>
 *
 * @method PaiementStock|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaiementStock|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaiementStock[]    findAll()
 * @method PaiementStock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaiementStockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaiementStock::class);
    }

//    /**
//     * @return PaiementStock[] Returns an array of PaiementStock objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PaiementStock
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
