<?php

namespace App\Controller;

use App\Service\MeteoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MeteoController extends AbstractController
{

    public function __construct(private MeteoService $meteoService)
    {
    }

    #[Route('/meteo/getSemaine', methods: ['GET'])]
    public function getMeteoSemaine(Request $req)
    {
        $date = $req->query->get('date');

        try {
            $retour = $this->meteoService->getMeteoSemaine($date);
            return new JsonResponse($retour, 200, [], true);
        } catch (\Throwable $th) {
            return new JsonResponse($th->getMessage(), 500);
        }
    }

    #[Route('/meteo/revalidate', methods: ['GET'])]
    public function revalidateSemaine(Request $req)
    {
        $date = $req->query->get("date");
        if (!$date) {
            return new JsonResponse("Unspecified date", 400);
        }
        try {
            $retour = $this->meteoService->revalidate($date);
            return new JsonResponse($retour, 200, [], true);
        } catch (\Throwable $th) {
            return new JsonResponse($th->getMessage(), 500);
        }
    }

    #[Route('/meteoListeGenerations', methods: ['GET'])]
    public function getMeteoGenerations(Request $req)
    {
        $mois = $req->query->get('mois');
        if (!$mois) {
            return new JsonResponse("Unspecified mois", 400);
        }
        return new JsonResponse($this->meteoService->getListeGenerations($mois));
    }

    #[Route('/meteo/{filename}', methods: ['GET'])]
    public function getByFileName($filename)
    {
        try {
            $retour = $this->meteoService->getByFileName($filename);
            return new JsonResponse($retour, 200, [], true);
        } catch (\Throwable $th) {
            return new JsonResponse($th->getMessage(), 500);
        }
    }
}
