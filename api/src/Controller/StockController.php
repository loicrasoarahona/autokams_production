<?php

namespace App\Controller;

use App\Entity\ApprovisionnementDetail;
use App\Service\StockService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class StockController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private StockService $stockService,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/approvisionnement/stockEnCours/{produitId}', methods: ['GET'])]
    public function getApprovisionnementDetailEnCours($produitId)
    {
        // cette fonction va retourner un approvisionnement detail

        $retour = $this->stockService->getStockEnCours($produitId);

        if ($retour == null) {
            return new JsonResponse("null", 200, [], true);
        }

        $normalized = $this->serializer->normalize($retour, null, ['groups' => ['approvisionnementDetail:collection', 'approvisionnement:collection', 'quantification:collection']]);

        return new JsonResponse($normalized);
    }

    #[Route('/approvisionnement/stockExistants/{produitId}', methods: ['GET'])]
    public function getApprovisionnementDetailsExistants($produitId)
    {
        $retour = $this->stockService->getApprovisionnementDetailsExistants($produitId);

        $normalized = $this->serializer->normalize($retour, null, ['groups' => ['approvisionnementDetail:collection', 'approvisionnement:collection', 'quantification:collection']]);
        return new JsonResponse($normalized);
    }


    public function getQuantiteRestanteApprovisionnementDetail($approvisionnementDetailId)
    {
        try {
            $retour = $this->stockService->quantiteRestanteApprovisionnementDetail($approvisionnementDetailId);
            return new JsonResponse($retour);
        } catch (\Throwable $th) {
            return new JsonResponse($th->getMessage(), 500);
        }
    }
}
