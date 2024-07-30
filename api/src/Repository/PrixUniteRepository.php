<?php

namespace App\Repository;

use App\Entity\PrixUnite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrixUnite>
 *
 * @method PrixUnite|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrixUnite|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrixUnite[]    findAll()
 * @method PrixUnite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrixUniteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrixUnite::class);
    }

//    /**
//     * @return PrixUnite[] Returns an array of PrixUnite objects
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

//    public function findOneBySomeField($value): ?PrixUnite
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
