<?php

namespace App\Repository;

use App\Entity\NotificationRecouvrement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NotificationRecouvrement>
 *
 * @method NotificationRecouvrement|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotificationRecouvrement|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotificationRecouvrement[]    findAll()
 * @method NotificationRecouvrement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotificationRecouvrementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotificationRecouvrement::class);
    }

//    /**
//     * @return NotificationRecouvrement[] Returns an array of NotificationRecouvrement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('n.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?NotificationRecouvrement
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
