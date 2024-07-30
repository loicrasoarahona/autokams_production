<?php

namespace App\Repository;

use App\Entity\PointDepart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PointDepart>
 *
 * @method PointDepart|null find($id, $lockMode = null, $lockVersion = null)
 * @method PointDepart|null findOneBy(array $criteria, array $orderBy = null)
 * @method PointDepart[]    findAll()
 * @method PointDepart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PointDepartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PointDepart::class);
    }

//    /**
//     * @return PointDepart[] Returns an array of PointDepart objects
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

//    public function findOneBySomeField($value): ?PointDepart
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
