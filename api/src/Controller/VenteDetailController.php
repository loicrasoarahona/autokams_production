<?php

namespace App\Controller;

use App\Entity\VenteDetail;
use App\Service\VenteDetailService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class VenteDetailController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private VenteDetailService $venteDetailService
    ) {
    }

    #[Route('/venteDetail/stockRelie/{id}', methods: ['GET'])]
    public function getPrixAchat($id)
    {
        $venteDetail = $this->em->getRepository(VenteDetail::class)->find($id);
        if (empty($venteDetail)) {
            return new JsonResponse('vente inexistant', 404);
        }

        try {
            $retour = $this->venteDetailService->getStockRelie($venteDetail);
        } catch (\Throwable $th) {
            if ($th->getCode() == VenteDetailService::INSUFFICIENT_STOCK) {
                return new JsonResponse('Stock insuffisant', 500);
            } else {
                throw $th;
            }
        }

        return new JsonResponse($retour);
    }
}
