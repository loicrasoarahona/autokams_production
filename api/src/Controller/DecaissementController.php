<?php

namespace App\Controller;

use App\Service\DecaissementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DecaissementController extends AbstractController
{

    public function __construct(
        private DecaissementService $decaissementService,
        private Security $security
    ) {
    }

    #[Route('/decaissements/totalParJour', methods: ['GET'])]
    public function totalParJour(Request $request)
    {
        $date = $request->query->get('date');
        $user = $this->security->getUser();
        $pointDeVente = $user->getPointDeVente();

        $result = $this->decaissementService->getTotalParJour($date, $pointDeVente);

        return new JsonResponse($result);
    }

    #[Route('/decaissements/byRepport', methods: ['GET'])]
    public function decaissementsByRepport(Request $request)
    {
        $page = $request->query->get('page');
        $date = $request->query->get('date');
        $voirTout = null;
        $voirTout = $request->query->get('voirTout');
        $user = $this->security->getUser();
        $pointDeVente = $user->getPointDeVente();

        if (empty($page)) {
            $page = 1;
        }

        if (!empty($date)) {
            $date = new \DateTime($date);
        } else {
            $date = new \DateTime();
        }



        $result = $this->decaissementService->getDecaissementsByRepport($pointDeVente, $date, $page, 30, $voirTout);


        return new JsonResponse($result);
    }
}
