<?php

namespace App\Repository;

use App\Entity\VenteLivraisonDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<VenteLivraisonDetail>
 *
 * @method VenteLivraisonDetail|null find($id, $lockMode = null, $lockVersion = null)
 * @method VenteLivraisonDetail|null findOneBy(array $criteria, array $orderBy = null)
 * @method VenteLivraisonDetail[]    findAll()
 * @method VenteLivraisonDetail[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VenteLivraisonDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, VenteLivraisonDetail::class);
    }

//    /**
//     * @return VenteLivraisonDetail[] Returns an array of VenteLivraisonDetail objects
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

//    public function findOneBySomeField($value): ?VenteLivraisonDetail
//    {
//        return $this->createQueryBuilder('v')
//            ->andWhere('v.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
