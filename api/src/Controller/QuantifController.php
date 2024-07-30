<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class QuantifController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }

    #[Route('/prix_equivalence/{produit_id}', name: 'setPrixEquivalence', methods: ['POST'])]
    public function setPrixEquivalence($produit_id, Request $request)
    {
        $quantification_id = $request->query->get('quantification_id');
        if (!$quantification_id) {
            return new JsonResponse(["message" => "quantification invalide"], 400);
        }

        $body = $request->getContent();
        $prix = json_decode($body)->prix;
        if (!$prix) {
            return new JsonResponse(["message" => "valeur invalide"], 400);
        }

        // recherche des quantifications equivalences
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from('App\Entity\QuantificationEquivalence', 'e')
            ->join('e.quantification', 'quantif')
            ->join('e.produit', 'pr')
            ->where('pr.id = :produitId and quantif.id = :quantificationId')
            ->setParameter(':produitId', $produit_id)
            ->setParameter(':quantificationId', $quantification_id);

        $quantifEquivals = $queryBuilder->getQuery()->getResult();

        foreach ($quantifEquivals as $element) {
            $element->setPrix($prix);
            $this->entityManager->flush();
        }


        return $this->json("");
    }
}
