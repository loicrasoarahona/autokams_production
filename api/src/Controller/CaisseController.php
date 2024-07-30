<?php

namespace App\Controller;

use App\Service\PaiementService;
use App\Service\RepportCaisseService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CaisseController extends AbstractController
{

    public function __construct(
        private Security $security,
        private PaiementService $paiementService,
        private SerializerInterface $serializer,
        private RepportCaisseService $repportCaisseService
    ) {
    }

    #[Route('/caisse/totalPaiementByRepport', methods: ['GET'])]
    public function totalByRepport(Request $request)
    {

        $date = $request->query->get('date');

        $user = $this->security->getUser();
        $pointDeVente = $user->getPointDeVente();
        $retour = null;

        if ($date) {
            $dateDate = new DateTime($date);
            $total = $this->paiementService->getTotalByRepport($pointDeVente, $dateDate);
            $lastRepport = $this->repportCaisseService->findLast($pointDeVente, $dateDate);
            $normalizedRepport = $this->serializer->normalize($lastRepport);
            $retour = ["totalPaiement" => $total, "lastRepport" => $normalizedRepport];
        } else {
            $total = $this->paiementService->getTotalByRepport($pointDeVente);
            $lastRepport = $this->repportCaisseService->findLast($pointDeVente);
            $normalizedRepport = $this->serializer->normalize($lastRepport);
            $retour = ["totalPaiement" => $total, "lastRepport" => $normalizedRepport];
        }

        return new JsonResponse($retour);
    }
}
