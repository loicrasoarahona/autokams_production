<?php

namespace App\Service;

use App\Entity\PointDeVente;
use App\Entity\Vente;
use App\Entity\VenteDetail;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class VenteService
{

    const UNPAYED_VENTE = 45;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private StockService $stockService,
        private VenteDetailService $venteDetailService,
        private LoggerInterface $logger
    ) {
    }

    public function getStocksRelies(Vente $vente)
    {
        $retour = [];
        $venteDetails = $vente->getVenteDetails();
        foreach ($venteDetails as $detailElement) {
            try {
                $stocksRelie = $this->venteDetailService->getStockRelie($detailElement);
                // concaténer
                $retour = array_merge($retour, $stocksRelie);
            } catch (\Throwable $th) {
                $this->logger->error($th->getMessage());
            }
        }
        return $retour;
    }

    public function getBeneficesByDate(PointDeVente $pointDeVente, DateTime $date1, DateTime $date2, $setUpBenefice = true)
    {
        $ventes = $this->getVentesWithoutPagination($pointDeVente, $date1, $date2);
        $retour = [];
        foreach ($ventes as $venteElement) {
            try {
                if (!$venteElement->isPayed()) {
                    array_push($retour, ["vente" => $venteElement, "benefice" => 0, "status" => "UNPAYED_VENTE"]);
                    continue;
                }
                $benefice = $this->getBenefice($venteElement, $setUpBenefice);
                array_push($retour, ["vente" => $venteElement, "benefice" => $benefice, "status" => "OK"]);
            } catch (\Throwable $th) {
                if ($th->getCode() == VenteDetailService::INSUFFICIENT_STOCK) {
                    array_push($retour, ["vente" => $venteElement, "benefice" => 0, "status" => "INSUFFICIENT_STOCK"]);
                }
            }
        }
        return $retour;
    }

    public function getVentesWithoutPagination(PointDeVente $pointDevente, DateTime $date1, DateTime $date2)
    {
        $results = $this->entityManager->getRepository(Vente::class)->createQueryBuilder('vente')
            ->select()
            ->join('vente.pointDeVente', 'pointDeVente')
            ->where('vente.daty>=:date1')
            ->andWhere('vente.daty<:date2')
            ->andWhere('pointDeVente.id=:pointDeVenteId')
            ->setParameters([
                "date1" => $date1,
                "date2" => $date2,
                "pointDeVenteId" => $pointDevente->getId()
            ])
            ->addOrderBy('vente.daty', 'desc')
            ->getQuery()
            ->getResult();

        return $results;
    }

    public function getBenefice(Vente $vente, $setUpBenefice = true)
    {
        if ($vente->isPayed()) {
            $retour = 0;
            // benefice de chaque venteDetail
            foreach ($vente->getVenteDetails() as $vd) {
                try {
                    $retour += $this->venteDetailService->getBenefice($vd, $setUpBenefice);
                } catch (\Throwable $th) {
                    $this->logger->error($th->getMessage());
                    throw $th;
                }
            }
            return $retour;
        } else {
            throw new Exception("UNPAYED_VENTE", $this::UNPAYED_VENTE);
        }
    }

    public function saveVente(Vente $vente)
    {
        // set up the approvisionnemnetDetails
        $details = $vente->getVenteDetails();
        if (!empty($details)) {
            foreach ($details as $detailElement) {
                try {
                    $this->setUpVenteDetailADs($detailElement);
                } catch (\Throwable $th) {
                    continue;
                }
            }
        }

        // enregitrer la vente et 
        // $this->entityManager->getRepository(Vente::class)->save($vente);
        $this->entityManager->persist($vente);
        $this->entityManager->flush();

        // retourner l'objet sauvegardé
        return $vente;
    }

    private function setUpVenteDetailADs(VenteDetail $venteDetail)
    {
        $dateVente = $venteDetail->getVente()->getDaty();
    }

    public function nextNumFacture()
    {
        $query = $this->entityManager->createQuery("select max(vente.numFacture) from App\Entity\Vente vente");
        $result = $query->getSingleScalarResult();
        if ($result == null) {
            $result = 0;
        }
        return  $result + 1;
    }

    public function resteAPayer(Vente $vente)
    {
        $retour = $this->getPrixTotal($vente) - $this->getPaiementTotal($vente);
        return $retour;
    }

    public function getPrixTotal(Vente $vente)
    {
        $venteDetails = $vente->getVenteDetails();
        $retour = 0;
        foreach ($venteDetails as $row) {
            $retour += $row->getPrixTotal();
        }

        return $retour;
    }

    public function getPaiementTotal(Vente $vente)
    {
        $paiements = $vente->getPaiements();
        $retour = 0;

        foreach ($paiements as $row) {
            $retour += $row->getMontant();
        }

        return $retour;
    }
}
