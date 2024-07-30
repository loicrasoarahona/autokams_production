<?php

namespace App\Repository;

use App\Entity\QuantificationEquivalence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QuantificationEquivalence>
 *
 * @method QuantificationEquivalence|null find($id, $lockMode = null, $lockVersion = null)
 * @method QuantificationEquivalence|null findOneBy(array $criteria, array $orderBy = null)
 * @method QuantificationEquivalence[]    findAll()
 * @method QuantificationEquivalence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuantificationEquivalenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QuantificationEquivalence::class);
    }

//    /**
//     * @return QuantificationEquivalence[] Returns an array of QuantificationEquivalence objects
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

//    public function findOneBySomeField($value): ?QuantificationEquivalence
//    {
//        return $this->createQueryBuilder('q')
//            ->andWhere('q.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
