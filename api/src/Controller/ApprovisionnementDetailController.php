<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Service\ApprovisionnementDetailService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApprovisionnementDetailController extends AbstractController
{
    public function __construct(private ApprovisionnementDetailService $approvisionnementDetailService, private EntityManagerInterface $em)
    {
    }

    #[Route('/approvisionnement_details/adExistants/{id}', methods: ['GET'])]
    public function adExistants($id, Request $request)
    {
        $date = $request->query->get('date', null);
        $dateDate = new DateTime($date);

        $produit = $this->em->getRepository(Produit::class)->find($id);
        if (empty($produit)) {
            return new JsonResponse('Produit non trouvé', 404);
        }

        $retour = $this->approvisionnementDetailService->getAdExistants($produit, $dateDate);

        return new JsonResponse($retour);
    }

    #[Route('/approvisionnement_details/quantiteRestante/{id}', methods: ['GET'])]
    public function quantiteRestante($id)
    {
        try {
            $retour = $this->approvisionnementDetailService->quantiteRestante($id);
            return new JsonResponse($retour);
        } catch (\Throwable $th) {
            return new JsonResponse($th->getMessage(), 500);
        }
    }
}
