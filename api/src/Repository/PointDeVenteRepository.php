<?php

namespace App\Repository;

use App\Entity\PointDeVente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PointDeVente>
 *
 * @method PointDeVente|null find($id, $lockMode = null, $lockVersion = null)
 * @method PointDeVente|null findOneBy(array $criteria, array $orderBy = null)
 * @method PointDeVente[]    findAll()
 * @method PointDeVente[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PointDeVenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PointDeVente::class);
    }

//    /**
//     * @return PointDeVente[] Returns an array of PointDeVente objects
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

//    public function findOneBySomeField($value): ?PointDeVente
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
