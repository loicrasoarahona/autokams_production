<?php

namespace App\Controller;

use App\Entity\Fournisseur;
use App\Service\FournisseurService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class FournisseurController extends AbstractController
{
    public function __construct(private FournisseurService $fournisseurService, private EntityManagerInterface $em)
    {
    }

    #[Route('/fournisseurs/recouvrementInfos/{id}', methods: ['GET'])]
    public function recouvrementInfos($id)
    {
        $fournisseur = $this->em->getRepository(Fournisseur::class)->find($id);

        if (empty($fournisseur)) {
            return new JsonResponse(["message" => "Entité introuvable"], 404);
        }

        $recouvrementInfos = $this->fournisseurService->getRecouvrementInfos($fournisseur);

        return new JsonResponse($recouvrementInfos);
    }
}
