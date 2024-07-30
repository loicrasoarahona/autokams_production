<?php

namespace App\Repository;

use App\Entity\Decaisseur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Decaisseur>
 *
 * @method Decaisseur|null find($id, $lockMode = null, $lockVersion = null)
 * @method Decaisseur|null findOneBy(array $criteria, array $orderBy = null)
 * @method Decaisseur[]    findAll()
 * @method Decaisseur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DecaisseurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Decaisseur::class);
    }

    //    /**
    //     * @return Decaisseur[] Returns an array of Decaisseur objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }
    /**
     * @return Decaisseur[] Returns an array of Decaisseur objects
     */
    public function findByNom($nom): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.nom = :val')
            ->setParameter('val', $nom)
            ->orderBy('d.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    public function findOneBySomeField($value): ?Decaisseur
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
