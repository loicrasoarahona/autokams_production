<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Service\ProduitService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BaseController extends AbstractController
{
    public function __construct(private ProduitService $produitService, private EntityManagerInterface $em)
    {
    }

    #[Route('/base', methods: ['GET'])]
    public function index()
    {
        $results = $this->em->getRepository(Produit::class)->createQueryBuilder('produit')->select()->getQuery()->getResult();
        dd($results);

        dump("hello");
        return $this->render('base.html.twig');
    }
}
