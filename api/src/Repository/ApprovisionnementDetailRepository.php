<?php

namespace App\Repository;

use App\Entity\ApprovisionnementDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ApprovisionnementDetail>
 *
 * @method ApprovisionnementDetail|null find($id, $lockMode = null, $lockVersion = null)
 * @method ApprovisionnementDetail|null findOneBy(array $criteria, array $orderBy = null)
 * @method ApprovisionnementDetail[]    findAll()
 * @method ApprovisionnementDetail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApprovisionnementDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApprovisionnementDetail::class);
    }

//    /**
//     * @return ApprovisionnementDetail[] Returns an array of ApprovisionnementDetail objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ApprovisionnementDetail
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
