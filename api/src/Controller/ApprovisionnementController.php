<?php

namespace App\Controller;

use App\Service\ApprovisionnementDetailService;
use App\Service\ApprovisionnementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApprovisionnementController extends AbstractController
{
    public function __construct(
        private ApprovisionnementService $approvisionnementService,
        private ApprovisionnementDetailService $approvisionnementDetailService
    ) {
    }

    #[Route('/produits/quantite/{produitId}', methods: ['GET'])]
    public function quantiteProduit($produitId)
    {
        try {
            $retour = $this->approvisionnementService->estimationStock($produitId);;
            return new JsonResponse($retour);
        } catch (\Throwable $th) {
            return new JsonResponse($th->getMessage(), 500);
        }
    }

    #[Route('/approvisionnement/quantiteRestante/{id}', methods: ['GET'])]
    public function quantiteRestanteApprovisionnementDetail($id)
    {
        try {
            $retour = $this->approvisionnementDetailService->quantiteRestante($id);
            return new JsonResponse($retour);
        } catch (\Throwable $th) {
            return new JsonResponse($th->getMessage(), 500);
        }
    }
}
