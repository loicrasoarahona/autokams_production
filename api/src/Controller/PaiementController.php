<?php

namespace App\Controller;

use App\Service\PaiementService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class PaiementController extends AbstractController
{

    public function __construct(
        private PaiementService $paiementService,
        private Security $security,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/paiements/totalParJour', methods: ['GET'])]
    public function totalParJour(Request $request)
    {
        $date = $request->query->get('date');
        $user = $this->security->getUser();
        $pointDeVente = $user->getPointDeVente();

        $result = $this->paiementService->getTotalParJour($date, $pointDeVente);

        return new JsonResponse($result);
    }

    #[Route('/paiements/byRepport', methods: ['GET'])]
    public function paiementsByRepport(Request $request)
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


        $result = $this->paiementService->getPaiementsByRepport($pointDeVente, $date, $page, 30, $voirTout);


        return new JsonResponse($result);
    }
}
