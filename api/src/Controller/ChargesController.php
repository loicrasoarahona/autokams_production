<?php

namespace App\Controller;

use App\Service\ChargeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ChargesController extends AbstractController
{
    public function __construct(private Security $security, private ChargeService $chargeService)
    {
    }

    #[Route('/charges/sommeTotal', methods: ['GET'])]
    public function sommeTotal(Request $request)
    {
        $user = $this->security->getUser();
        $pointDeVente = $user->getPointDeVente();

        $dateDebut = new \DateTime($request->query->get('dateDebut'), null);
        $dateFin = new \DateTime($request->query->get('dateFin'), null);

        if (empty($pointDeVente)) {
            return new JsonResponse("Vous n'avez pas de point de vente", 401);
        }

        $retour = $this->chargeService->getSommeCharge($pointDeVente, $dateDebut, $dateFin);
        return new JsonResponse($retour, 200);
    }

    #[Route('/charges/withDecaissements', methods: ['GET'])]
    public function chargesWithDecaissements(Request $request)
    {
        $user = $this->security->getUser();
        $pointDeVente = $user->getPointDeVente();

        $dateDebut = new \DateTime($request->query->get('dateDebut'), null);
        $dateFin = new \DateTime($request->query->get('dateFin'), null);

        if (empty($pointDeVente)) {
            return new JsonResponse("Vous n'avez pas de point de vente", 401);
        }
        $retour = $this->chargeService->getChargesWithDecaissements($pointDeVente, $dateDebut, $dateFin);

        return new JsonResponse($retour, 200);
    }
}
