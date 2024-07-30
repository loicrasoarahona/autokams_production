<?php

namespace App\Controller;

use App\Entity\Client;
use App\Service\ClientService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ClientController extends AbstractController
{
    public function __construct(
        private ClientService $clientService,
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/clients/recouvrements/{id}', methods: ['GET'])]
    public function etatRecouvrementClient($id)
    {
        $client = $this->em->getRepository(Client::class)->find($id);
        if (empty($client)) {
            return new JsonResponse(['message' => 'Client not found'], 404);
        }

        $ventes = $this->clientService->getVentesNonPayer($client);
        $resteAPayer = $this->clientService->getResteAPayer($client);

        $normalizedVentes = $this->serializer->normalize($ventes, null, ['groups' => ["vente:post", "client:collection", "venteLivraison:collection", "vente:recouvrement"]]);

        $retour = [
            'resteAPayer' => $resteAPayer,
            'ventes' => $normalizedVentes
        ];
        return new JsonResponse($retour);
    }

    #[Route('/clients/verser/{id}', methods: ['POST'])]
    public function verser($id, Request $request)
    {
        $client = $this->em->getRepository(Client::class)->find($id);
        if (empty($client)) {
            return new JsonResponse(['message' => 'Client not found'], 404);
        }

        // get body
        $data = json_decode($request->getContent(), true);
        $montant = $data['montant'];

        if (empty($montant)) {
            return new JsonResponse(['message' => 'Montant obligatoire'], 400);
        }

        $this->clientService->verserMontant($client, $montant);

        return new JsonResponse(['message' => 'Versement effectué']);
    }
}
