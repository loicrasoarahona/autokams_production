<?php

namespace App\Controller;

use App\Service\BilanVenteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BilanVenteController extends AbstractController
{
    public function __construct(private Security $security, private BilanVenteService $bilanVenteService)
    {
    }

    #[Route('/bilanVente/paiements', methods: ['GET'])]
    public function paiements(Request $request)
    {
        $dateDebut = $request->query->get('dateDebut');
        $dateFin = $request->query->get('dateFin');
        $page = $request->query->get('page', 1);
        $user = $this->security->getUser();
        $pointDeVente = $user->getPointDeVente();

        if (empty($pointDeVente)) {
            return new JsonResponse(['message' => 'Vous n\'avez pas de point de vente'], 401);
        }

        if (empty($dateDebut) || empty($dateFin)) {
            return new JsonResponse(['message' => 'Les dates de début et de fin sont obligatoires'], Response::HTTP_BAD_REQUEST);
        }

        $dateDebutDate = new \DateTime($dateDebut);
        $dateDebutDate->setTime(0, 0, 0);
        $dateFinDate = new \DateTime($dateFin);
        $dateFinDate->setTime(23, 59, 59);

        $retour = $this->bilanVenteService->getPaiements($pointDeVente, $dateDebutDate, $dateFinDate, $page);

        return new JsonResponse($retour);
    }

    #[Route('/bilanVente/totalPaiements', methods: ['GET'])]
    public function totalByRepport(Request $request)
    {
        $dateDebut = $request->query->get('dateDebut');
        $dateFin = $request->query->get('dateFin');
        $user = $this->security->getUser();
        $pointDeVente = $user->getPointDeVente();

        if (empty($pointDeVente)) {
            return new JsonResponse(['message' => 'Vous n\'avez pas de point de vente'], 401);
        }

        if (empty($dateDebut) || empty($dateFin)) {
            return new JsonResponse(['message' => 'Les dates de début et de fin sont obligatoires'], Response::HTTP_BAD_REQUEST);
        }

        $dateDebutDate = new \DateTime($dateDebut);
        $dateDebutDate->setTime(0, 0, 0);
        $dateFinDate = new \DateTime($dateFin);
        $dateFinDate->setTime(23, 59, 59);

        $retour = $this->bilanVenteService->getSommePaiements($pointDeVente, $dateDebutDate, $dateFinDate);

        return new JsonResponse($retour);
    }
}
