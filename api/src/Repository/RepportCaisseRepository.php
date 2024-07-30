<?php

namespace App\Repository;

use App\Entity\RepportCaisse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RepportCaisse>
 *
 * @method RepportCaisse|null find($id, $lockMode = null, $lockVersion = null)
 * @method RepportCaisse|null findOneBy(array $criteria, array $orderBy = null)
 * @method RepportCaisse[]    findAll()
 * @method RepportCaisse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RepportCaisseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RepportCaisse::class);
    }

//    /**
//     * @return RepportCaisse[] Returns an array of RepportCaisse objects
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

//    public function findOneBySomeField($value): ?RepportCaisse
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
