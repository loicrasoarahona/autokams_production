<?php

namespace App\Repository;

use App\Entity\VenteDetailApprovisionnementDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VenteDetailApprovisionnementDetail>
 *
 * @method VenteDetailApprovisionnementDetail|null find($id, $lockMode = null, $lockVersion = null)
 * @method VenteDetailApprovisionnementDetail|null findOneBy(array $criteria, array $orderBy = null)
 * @method VenteDetailApprovisionnementDetail[]    findAll()
 * @method VenteDetailApprovisionnementDetail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VenteDetailApprovisionnementDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VenteDetailApprovisionnementDetail::class);
    }

//    /**
//     * @return VenteDetailApprovisionnementDetail[] Returns an array of VenteDetailApprovisionnementDetail objects
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

//    public function findOneBySomeField($value): ?VenteDetailApprovisionnementDetail
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
