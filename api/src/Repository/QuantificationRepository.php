<?php

namespace App\Repository;

use App\Entity\Quantification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quantification>
 *
 * @method Quantification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quantification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quantification[]    findAll()
 * @method Quantification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuantificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quantification::class);
    }

//    /**
//     * @return Quantification[] Returns an array of Quantification objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('q.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Quantification
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
