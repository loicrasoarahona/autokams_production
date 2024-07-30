<?php

namespace App\Repository;

use App\Entity\RepportANouveau;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RepportANouveau>
 *
 * @method RepportANouveau|null find($id, $lockMode = null, $lockVersion = null)
 * @method RepportANouveau|null findOneBy(array $criteria, array $orderBy = null)
 * @method RepportANouveau[]    findAll()
 * @method RepportANouveau[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepportANouveauRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RepportANouveau::class);
    }

//    /**
//     * @return RepportANouveau[] Returns an array of RepportANouveau objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RepportANouveau
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
