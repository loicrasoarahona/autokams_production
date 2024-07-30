<?php

namespace App\Repository;

use App\Entity\VenteDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VenteDetail>
 *
 * @method VenteDetail|null find($id, $lockMode = null, $lockVersion = null)
 * @method VenteDetail|null findOneBy(array $criteria, array $orderBy = null)
 * @method VenteDetail[]    findAll()
 * @method VenteDetail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VenteDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VenteDetail::class);
    }

//    /**
//     * @return VenteDetail[] Returns an array of VenteDetail objects
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

//    public function findOneBySomeField($value): ?VenteDetail
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
