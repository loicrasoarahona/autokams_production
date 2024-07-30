<?php

namespace App\Controller;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use App\Entity\NotificationRecouvrement;
use App\Entity\Paiement;
use App\Entity\Utilisateur;
use App\Entity\Vente;
use App\Service\VenteDetailService;
use App\Service\VenteService;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

class VenteController extends AbstractController
{

    public function __construct(
        private VenteService $venteService,
        private SerializerInterface $serializer,
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {
    }

    #[Route('/ventes/stocksRelies/{id}', methods: ['GET'])]
    public function stocksRelies($id)
    {
        $vente = $this->entityManager->getRepository(Vente::class)->find($id);

        if (empty($vente)) {
            return new JsonResponse('Vente not found', 404);
        }

        $retour = $this->venteService->getStocksRelies($vente);

        return new JsonResponse($retour);
    }

    #[Route('/ventes/beneficeByDate', methods: ['GET'])]
    public function beneficesByDate(Request $request)
    {
        $setUpBenefice = $request->query->get('revalidate', true);
        $date1 = $request->query->get('dateDebut');
        $date2 = $request->query->get('dateFin');

        $utilisateur = $this->security->getUser();
        $pointDeVente = $utilisateur->getPointDeVente();

        if (empty($pointDeVente)) {
            return new JsonResponse("Utilisateur invalide", 401);
        }

        if (empty($date1) || empty($date2)) {
            return new JsonResponse("Unspecified dateDebut dateFin", 400);
        }

        $date1Date = new DateTime($date1);
        $date2Date = new DateTime($date2);

        $retour = $this->venteService->getBeneficesByDate($pointDeVente, $date1Date, $date2Date, $setUpBenefice);

        $normalizedRetour = $this->serializer->normalize($retour, null, ['groups' => ["client:collection", "vente:post", "client:collection", "venteDetail:collection", "produit:collection", "quantification:collection", "categorie:collection", "paiement:collection"]]);

        return new JsonResponse($normalizedRetour);
    }

    #[Route('/ventes/benefice/{id}', methods: ['GET'])]
    public function benefice($id)
    {
        $vente = $this->entityManager->getRepository(Vente::class)->find($id);
        if (empty($vente)) {
            return new JsonResponse('vente inexistant', 404);
        }

        try {
            $retour = $this->venteService->getBenefice($vente);
        } catch (\Throwable $th) {
            if ($th->getCode() == VenteDetailService::INSUFFICIENT_STOCK) {
                return new JsonResponse('Stock insuffisant', 500);
            } else if ($th->getCode() == VenteService::UNPAYED_VENTE) {
                return new JsonResponse('Vente non payée', 500);
            } else {
                throw $th;
            }
        }


        return new JsonResponse($retour);
    }

    #[Route('/ventes/save', methods: ['POST'])]
    public function saveVente(Request $request)
    {
        $data = $request->getContent();

        $vente = $this->serializer->deserialize($data, Vente::class, "json", ['groups' => ['vente:post', 'paiement:collection', 'venteDetail:collection']]);

        $retour = $this->venteService->saveVente($vente);

        $normalized = $this->serializer->normalize($retour, null, ['groups' => ["vente:post", "client:collection", "venteDetail:collection", "venteLivraison:collection", "produit:collection", "paiement:collection"]]);

        return new JsonResponse($normalized, 201);
    }

    #[Route('/ventes/deletePaiements/{venteId}', methods: ['DELETE'])]
    public function deletePaiements($venteId)
    {
        $result = $this->entityManager->getRepository(Paiement::class)->createQueryBuilder('paiement')
            ->join('paiement.vente', 'vente')
            ->where('vente.id = :venteId')
            ->setParameter('venteId', $venteId)

            ->getQuery()
            ->execute();


        foreach ($result as $paiement) {
            $this->entityManager->remove($paiement);
        }

        $this->entityManager->flush();

        return new JsonResponse("", 204);
    }

    #[Route('/notificationRecouvrement', methods: ['GET'])]
    public function notificationRecouvrement(Request $request)
    {
        $delay = $request->query->get("delay");
        if ($delay > 0) {
            $dateNow = new DateTime();
            $dateLimite = (new DateTime())->sub(new DateInterval('PT5H'));

            $queryBuilder = $this->entityManager->getRepository(NotificationRecouvrement::class)->createQueryBuilder('notif');
            $queryBuilder->where('notif.daty >=:dateLimite and notif.daty<=:dateNow');
            $queryBuilder->setParameter('dateLimite', $dateLimite);
            $queryBuilder->setParameter('dateNow', $dateNow);

            $result = $queryBuilder->getQuery()->getResult();

            // si une notif a été demandée, je regarde ce qui n'a pas été lu
            if (count($result)) {

                $queryBuilder->andWhere('notif.dateLecture is null');

                $result = $queryBuilder->getQuery()->getResult();
                // si une d'entre elles n'a pas été lu, je lui renvoie
                if (count($result)) {
                    $serialized = $this->serializer->normalize($result[0]);
                    return new JsonResponse($serialized, 200);
                }
                // si non, je n'ai rien à lui envoyer
                return new JsonResponse([], 200);
            }
            // si non, j'en crée une et je lui envoie
            else {
                $newNotification = new NotificationRecouvrement();
                $newNotification->setDaty(new DateTime());

                $this->entityManager->persist($newNotification);
                $this->entityManager->flush();

                $this->entityManager->refresh($newNotification);
                if ($newNotification) {
                    $serialized = $this->serializer->normalize($newNotification);

                    return new JsonResponse($serialized, 200);
                } else {
                    return new JsonResponse("Erreur de création de notification", 500);
                }
            }
        } else {
            return new JsonResponse("Veuillez préciser le délai", 400);
        }
    }

    #[Route('/ventes/nextNumFacture', methods: ['GET'])]
    public function nextNumFacture()
    {
        $retour = $this->venteService->nextNumFacture();

        return $this->json($retour);
    }

    #[Route('/ventes/availableNumFacture/{numFacture}', methods: ['GET'])]
    public function availableNumFacture($numFacture)
    {
        $queryBuilder = $this->entityManager->getRepository(Vente::class)->createQueryBuilder('vente');
        $queryBuilder->where('vente.numFacture=:numFacture');
        $queryBuilder->setParameter('numFacture', $numFacture);

        $result = $queryBuilder->getQuery()->getResult();

        $count = count($result);
        $retour = $count == 0;

        return $this->json($retour);
    }

    #[Route('/ventes', methods: ['GET'])]
    public function getVentes(Request $request)
    {
        $user = $this->security->getUser();
        if (!$user) {
            return new JsonResponse("Utilisateur inconnu", 500);
        }
        $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($user->getId());
        $pointDeVente = $utilisateur->getPointDeVente();
        if (!$pointDeVente) {
            return new JsonResponse("Point de vente indéfini", 500);
        }

        $numFacture = $request->query->get("numFacture");
        $client = $request->query->get("client");
        $dateDebut = $request->query->get("dateDebut");
        $dateFin = $request->query->get("dateFin");
        $limit = $request->query->get("limit");
        $offset = $request->query->get("offset");
        $orderBy = $request->query->get("orderBy");
        $sort = $request->query->get("sort");

        $queryBuilder = $this->entityManager->getRepository(Vente::class)->createQueryBuilder('vente');

        $queryBuilder->where("1<2");
        $queryBuilder->join('vente.pointDeVente', 'pointDeVente');
        $queryBuilder->andWhere('pointDeVente.id=:pointDeVenteId');
        $queryBuilder->setParameter('pointDeVenteId', $pointDeVente->getId());

        if (!empty($numFacture)) {
            $queryBuilder->andWhere("vente.numFacture like :numFacture");
            $queryBuilder->setParameter("numFacture", $numFacture . "%");
        }

        $queryBuilder->leftJoin("vente.client", "client");
        if (!empty($client)) {

            $queryBuilder->andWhere("client.nom like :client");
            $queryBuilder->setParameter('client', '%' . $client . '%');
        }

        if (!empty($dateDebut)) {
            if (!empty($dateFin)) {
                $queryBuilder->andWhere("vente.daty >= :dateDebut and vente.daty <=:dateFin");
                $queryBuilder->setParameter('dateDebut', $dateDebut);
                $queryBuilder->setParameter('dateFin', $dateFin);
            } else {
                // ny ampitso
                $split = explode("-", explode(" ", $dateDebut)[0]);
                $split[2] =  intval($split[2]) + 1;
                $nyAmpitso = join("-", $split);

                $queryBuilder->andWhere("vente.daty >= :dateDebut and vente.daty<= :nyAmpitso");
                $queryBuilder->setParameter('dateDebut', $dateDebut);
                $queryBuilder->setParameter('nyAmpitso', $nyAmpitso);
            }
        }



        if (isset($orderBy)) {
            if (!isset($sort)) {
                $sort = "asc";
            }
            $queryBuilder->addOrderBy($orderBy, $sort);
        }

        $queryBuilder->addOrderBy("vente.daty", "desc");


        $result = $queryBuilder->getQuery()->getResult();

        $count = count($result);


        if (isset($limit) && isset($offset)) {
            $limit = intval($limit);
            $offset = intval($offset);

            $queryBuilder->setMaxResults($limit);
            $queryBuilder->setFirstResult($offset);
            $result = $queryBuilder->getQuery()->getResult();
        }

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups(["vente:post", "client:collection"])
            ->toArray();



        $rows = $this->serializer->normalize($result, "json", $context);

        $retour = array("count" => $count, "rows" => $rows);

        return new JsonResponse($retour, 200, []);
    }

    #[Route('/resteAPayer/{vente_id}', methods: ['GET'])]
    public function getResteAPayer($vente_id)
    {
        $query = $this->entityManager->createQuery("select sum(paie.montant) from App\Entity\Paiement paie join paie.vente vente where vente.id=:vente_id");
        $query->setParameter('vente_id', $vente_id);
        $result = $query->getSingleScalarResult();
        if ($result == null) {
            $result = 0;
        }
        $totalPayer = $result;

        $vente = $this->entityManager->getRepository(Vente::class)->find($vente_id);


        return $this->json($vente->getPrix() - $totalPayer);
    }

    #[Route('/totalPayer/{vente_id}', methods: ['GET'])]
    public function getTotalPayer($vente_id)
    {
        $query = $this->entityManager->createQuery("select sum(paie.montant) from App\Entity\Paiement paie join paie.vente vente where vente.id=:vente_id");
        $query->setParameter('vente_id', $vente_id);
        $result = $query->getSingleScalarResult();
        if ($result == null) {
            $result = 0;
        }
        $totalPayer = $result;

        return $this->json($totalPayer);
    }
}
