<?php

namespace App\Controller;

use App\Service\RepportNouveauService;
use App\Service\StockService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class RepportNouveauController extends AbstractController
{
    public function __construct(private StockService $stockService, private RepportNouveauService $repportNouveauService)
    {
    }

    #[Route('/repport_a_nouveaus/quantiteRestante/{id}', methods: ['GET'])]
    public function quantiteRestante($id)
    {
        try {
            $retour = $this->repportNouveauService->quantiteRestante($id);

            return new JsonResponse($retour, 200);
        } catch (\Throwable $th) {

            return new JsonResponse($th->getMessage(), 500);
        }
    }

    #[Route('/repport_a_nouveaus/quantiteRestanteByProduit/{produitId}', methods: ['GET'])]
    public function quantiteRestanteByProduit($produitId)
    {
        try {
            $retour = $this->repportNouveauService->quantiteRestanteParProduit($produitId);
            return new JsonResponse($retour);
        } catch (\Throwable $th) {
            return new JsonResponse($th->getMessage(), 500);
        }
    }
}
