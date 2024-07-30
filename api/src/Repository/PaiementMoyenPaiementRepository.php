<?php

namespace App\Repository;

use App\Entity\PaiementMoyenPaiement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaiementMoyenPaiement>
 *
 * @method PaiementMoyenPaiement|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaiementMoyenPaiement|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaiementMoyenPaiement[]    findAll()
 * @method PaiementMoyenPaiement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaiementMoyenPaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaiementMoyenPaiement::class);
    }

//    /**
//     * @return PaiementMoyenPaiement[] Returns an array of PaiementMoyenPaiement objects
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

//    public function findOneBySomeField($value): ?PaiementMoyenPaiement
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
