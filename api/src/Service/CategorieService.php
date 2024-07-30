<?php

namespace App\Service;

use App\Entity\Categorie;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class CategorieService
{
    public function __construct(private EntityManagerInterface $em, private SerializerInterface $serializer)
    {
    }

    public function findOrCreate(string $nom)
    {
        $result = $this->em->getRepository(Categorie::class)->createQueryBuilder("categorie")
            ->select()
            ->where("categorie.nom=:nom")
            ->setParameter("nom", $nom)
            ->getQuery()
            ->getResult();

        if (!empty($result[0])) {
            return $result[0];
        }

        // si elle n'existe pas, j'en crée une nouvelle
        $newCategorie = new Categorie();
        $newCategorie->setNom($nom);
        $this->em->persist($newCategorie);
        $this->em->flush();
        return $newCategorie;
    }
}
