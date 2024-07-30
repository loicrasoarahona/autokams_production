<?php

namespace App\Repository;

use App\Entity\RepportNouveau;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RepportNouveau>
 *
 * @method RepportNouveau|null find($id, $lockMode = null, $lockVersion = null)
 * @method RepportNouveau|null findOneBy(array $criteria, array $orderBy = null)
 * @method RepportNouveau[]    findAll()
 * @method RepportNouveau[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepportNouveauRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RepportNouveau::class);
    }

//    /**
//     * @return RepportNouveau[] Returns an array of RepportNouveau objects
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

//    public function findOneBySomeField($value): ?RepportNouveau
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
