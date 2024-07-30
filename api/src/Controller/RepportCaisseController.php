<?php

namespace App\Controller;

use App\Service\RepportCaisseService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class RepportCaisseController extends AbstractController
{
    public function __construct(
        private Security $security,
        private RepportCaisseService $repportCaisseService,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/repport_caisses/montantEnCaisse', methods: ['GET'])]
    public function montantEnCaisse(Request $request)
    {
        $user = $this->security->getUser();
        $pointDeVente = $user->getPointDeVente();

        $date = $request->query->get('date');
        $retour = null;

        if (!empty($date)) {
            $dateDate = new DateTime($date);
            $retour = $this->repportCaisseService->montantEnCaisse($pointDeVente, $dateDate);
        } else {
            $retour = $this->repportCaisseService->montantEnCaisse($pointDeVente);
        }

        return new JsonResponse($retour);
    }

    #[Route('/repport_caisses/lastByDate', methods: ['GET'])]
    public function lastRepport(Request $request)
    {
        $user = $this->security->getUser();
        $pointDeVente = $user->getPointDeVente();

        $date = $request->query->get('date');
        $retour = null;

        if (!empty($date)) {
            $dateDate = new DateTime($date);
            $retour = $this->repportCaisseService->findLast($pointDeVente, $dateDate);
        } else {
            $retour = $this->repportCaisseService->findLast($pointDeVente);
        }

        if (!empty($retour)) {
            $normalized = $this->serializer->normalize($retour);

            return new JsonResponse($normalized);
        } else {
            return new Response(null);
        }
    }
}
