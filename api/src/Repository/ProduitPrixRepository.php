<?php

namespace App\Repository;

use App\Entity\ProduitPrix;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProduitPrix>
 *
 * @method ProduitPrix|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProduitPrix|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProduitPrix[]    findAll()
 * @method ProduitPrix[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitPrixRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProduitPrix::class);
    }

//    /**
//     * @return ProduitPrix[] Returns an array of ProduitPrix objects
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

//    public function findOneBySomeField($value): ?ProduitPrix
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
