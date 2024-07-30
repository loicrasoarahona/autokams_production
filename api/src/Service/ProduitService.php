<?php

namespace App\Service;

use App\Entity\ApprovisionnementDetail;
use App\Entity\PointDepart;
use App\Entity\PointDeVente;
use App\Entity\Produit;
use App\Entity\RepportANouveau;
use App\Entity\VenteDetail;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProduitService
{
    private StockService $stockService;
    private ApprovisionnementService $approvisionnementService;
    private EntityManagerInterface $em;
    private SerializerInterface $serializer;
    private ApprovisionnementDetailService $approvisionnementDetailService;


    public function __construct(
        StockService $stockService,
        ApprovisionnementService $approvisionnementService,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        ApprovisionnementDetailService $approvisionnementDetailService,
        private LoggerInterface $logger
    ) {
        $this->stockService = $stockService;
        $this->approvisionnementService = $approvisionnementService;
        $this->approvisionnementService->setProduitService($this);
        $this->em = $em;
        $this->serializer = $serializer;
        $this->approvisionnementDetailService = $approvisionnementDetailService;
    }

    public function getProduitWithoutPagination(PointDeVente $pointDeVente)
    {
        $retour = $this->em->getRepository(Produit::class)->createQueryBuilder('produit')
            ->select()
            ->join('produit.pointDeVente', 'pointDeVente')
            ->where('pointDeVente.id=:pointDeVenteId')
            ->setParameter('pointDeVenteId', $pointDeVente->getId())
            ->getQuery()
            ->getResult();

        $normalized = $this->serializer->normalize($retour, null, ['groups' => ['quantificationEquivalence:collection', 'produit:collection', 'categorie:collection', 'quantification:collection']]);

        for ($i = 0; $i < count($normalized); $i++) {
            $lastPrixAchat = 0;
            try {
                $lastPrixAchat = $this->getLastPrixAchat($retour[$i]);
            } catch (\Throwable $th) {
                $this->logger->error($th->getMessage());
            }
            $normalized[$i]['prixAchat'] = $lastPrixAchat;
        }

        return $normalized;
    }

    public function getProduitsPagination($page = 1, $nbItem = 30, $filtre = null)
    {
        $query = $this->em->getRepository(Produit::class)->createQueryBuilder('produit')
            ->select()
            ->join('produit.pointDeVente', 'pointDeVente')
            ->leftJoin('produit.categorie', 'categorie')
            ->orderBy('produit.nom', 'asc');

        if ($filtre) {
            if (!empty($filtre["nom"])) {
                $query->andWhere('produit.nom like :nom')
                    ->setParameter('nom', '%' . $filtre["nom"] . '%');
            }
            if (!empty($filtre["categorieId"])) {
                $query->andWhere('categorie.id=:categorieId')
                    ->setParameter('categorieId', $filtre["categorieId"]);
            }
            if (!empty($filtre["pointDeVenteId"])) {
                $query->andWhere('pointDeVente.id=:pointDeVenteId')
                    ->setParameter('pointDeVenteId', $filtre["pointDeVenteId"]);
            }
        }

        $compte = count($query->getQuery()->getResult());

        $results = $query
            ->setFirstResult(($page - 1) * $nbItem)
            ->setMaxResults($nbItem)
            ->getQuery()
            ->getResult();

        return [
            "compte" => $compte,
            "results" => $results
        ];
    }

    public function getNbVenteDetails()
    {
    }


    public function deleteAllApprovisionnementDetails(Produit $produit)
    {
        $approvisionnementDetails = $this->em->getRepository(ApprovisionnementDetail::class)->createQueryBuilder('ad')
            ->select()
            ->join('ad.produit', 'produit')
            ->where('produit.id=:produitId')
            ->setParameter('produitId', $produit->getId())
            ->getQuery()
            ->getResult();

        foreach ($approvisionnementDetails as $ad) {
            $this->em->remove($ad->getApprovisionnement());
        }
        $this->em->flush();
    }

    public function getNbVentesByPointDepart(Produit $produit)
    {
        $quantiteInitiale = 0;
        $dateDepart = (new DateTime("1970-01-01"))->format('Y-m-d H:i:s');
        $pointDepart = $this->getPointDepart($produit);
        if (!empty($pointDepart)) {
            $dateDepart = $pointDepart->getDateVente()->format('Y-m-d H:i:s');
            if (!empty($pointDepart->getQuantiteInitiale())) {
                $quantiteInitiale += $pointDepart->getQuantiteInitiale();
            }
        }

        $venteDetailsAssoc = $this->getVenteDetailsAfterPointDepart($produit, $dateDepart);

        foreach ($venteDetailsAssoc as $row) {
            $quantiteInitiale += $row["quantite"];
        }

        return $quantiteInitiale;
    }

    public function getADbyPointDepart(Produit $produit)
    {
        $dateDepart = new DateTime("1970-01-01");
        $dateVenteDepart = new DateTime("1970-01-01");
        $quantiteInitiale = 0;
        $pointDepart = $this->getPointDepart($produit);
        if (!empty($pointDepart)) {
            $dateDepart = $pointDepart->getDateApprovisionnement();
            $dateVenteDepart = $pointDepart->getDateVente();
            $quantiteInitiale = $pointDepart->getQuantiteInitiale();
        }

        $results = $this->em->getRepository(ApprovisionnementDetail::class)->createQueryBuilder('ad')
            ->select()
            ->join('ad.produit', 'produit')
            ->join('ad.approvisionnement', 'approvisionnement')
            ->where('produit.id=:produitId')
            ->andWhere('approvisionnement.daty>=:dateDepart')
            ->setParameters(["produitId" => $produit->getId(), "dateDepart" => $dateDepart])
            ->addOrderBy('approvisionnement.daty', 'asc')
            ->getQuery()
            ->getResult();

        $normalized = $this->serializer->normalize($results, null, ["groups" => ["approvisionnementDetail:collection", "approvisionnement:collection", "quantification:collection"]]);


        // récupération de chaque niveau de stock
        // nbVentes
        $nbVentes = $this->getNbVentesAfterPointDepart($produit, $dateVenteDepart->format('Y-m-d H:i:s'));
        $nbVentesTotal = $nbVentes + $quantiteInitiale;
        for ($i = 0; $i < count($normalized); $i++) {
            $row = $normalized[$i];
            if ($nbVentesTotal > $row['quantite']) {
                $nbVentesTotal -= $row["quantite"];
                $normalized[$i]["reste"] = 0;
            } else {
                $normalized[$i]["reste"] = $row['quantite'] - $nbVentesTotal;
                $nbVentesTotal = 0;
            }
        }


        return $normalized;
    }

    public function getNbVentesAfterPointDepart(Produit $produit, $dateDepart = null)
    {
        $retour = 0;
        $venteDetailsAssoc = $this->getVenteDetailsAfterPointDepart($produit, $dateDepart);
        foreach ($venteDetailsAssoc as $row) {
            $retour += $row['quantite'];
        }
        return $retour;
    }

    public function getVenteDetailsAfterPointDepart(Produit $produit, $dateDepart = null)
    {
        // Cette fonction ne va pas retouner des vente_details proprement dites mais une combinaison de vente_details et
        // difference_stock sous la forme (produit_id, quantite, daty, quantification_id)

        if ($dateDepart == null) {
            $dateDepart = (new DateTime("1970-01-01"))->format('Y-m-d H:i:s');
            $pointDepart = $this->getPointDepart($produit);
            if (!empty($pointDepart)) {
                $dateDepart = $pointDepart->getDateVente()->format('Y-m-d H:i:s');
            }
        }
        $produitId = $produit->getId();

        $sql = "(select produit_id, quantite, vente.daty as daty, unite_id as quantification_id from vente_detail
        join vente on vente.id = vente_detail.vente_id
        where produit_id=" . $produitId . " and daty>'" . $dateDepart . "')
        union
        (select produit_id, -quantite as quantite, daty, quantification_id from difference_stock
        where produit_id=" . $produitId . " and daty>'" . $dateDepart . "')
        order by daty asc";

        $results = $this->em->getConnection()->fetchAllAssociative($sql);

        return $results;
    }

    public function getEstimationStockAfterPointDepart(Produit $produit, $dateDepart = null)
    {
        $nbVentes = $this->getNbVentesByPointDepart($produit);
        $adsAssoc = $this->getADbyPointDepart($produit);
        $total = 0;
        foreach ($adsAssoc as $row) {
            $total += $row['quantite'];
        }
        return $total - $nbVentes;
    }

    public function getApprovisionnementDetailsAfterPointDepart(Produit $produit, $dateDepart = null)
    {
        if ($dateDepart == null) {
            $dateDepart = (new DateTime("1970-01-01"))->format('Y-m-d H:i:s');
            $pointDepart = $this->getPointDepart($produit);
            if (!empty($pointDepart)) {
                $dateDepart = ($pointDepart->getDateApprovisionnement())->format('Y-m-d H:i:s');
            }
        }

        $result = $this->em->getRepository(ApprovisionnementDetail::class)->createQueryBuilder('ad')
            ->select()
            ->join('ad.produit', 'produit')
            ->join('ad.approvisionnement', 'approvisionnement')
            ->where('produit.id=:produitId')
            ->andWhere('approvisionnement.daty>=:dateApprovisionnement')
            ->setParameters(['produitId' => $produit->getId(), 'dateApprovisionnement' => $dateDepart])
            ->addOrderBy('approvisionnement.daty', 'asc')
            ->getQuery()
            ->getResult();

        return $result;
    }

    public function getApprovisionnementDetailByDate(Produit $produit, $date)
    {
        $result = $this->em->getRepository(ApprovisionnementDetail::class)->createQueryBuilder('ad')
            ->select()
            ->join('ad.produit', 'produit')
            ->join('ad.approvisionnement', 'approvisionnement')
            ->where('produit.id=:produitId')
            ->andWhere('approvisionnement.daty>=:dateApprovisionnement')
            ->setParameters(['produitId' => $produit->getId(), 'dateApprovisionnement' => $date])
            ->addOrderBy('approvisionnement.daty', 'asc')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if (!empty($result[0])) {
            return $result[0];
        }
        return null;
    }

    public function creerPointDepart(Produit $produit, $dateApprovisionnement)
    {
        $lastPointDepart = $this->getPointDepart($produit);
        $dateDepart = (new DateTime("1970-01-01"))->format('Y-m-d H:i:s');
        $dateVenteInitial = (new DateTime("1970-01-01"))->format('Y-m-d H:i:s');
        if (!empty($lastPointDepart)) {
            $dateDepart = $lastPointDepart->getDateApprovisionnement()->format('Y-m-d H:i:s');
            $dateVenteInitial = $lastPointDepart->getDateVente()->format('Y-m-d H:i:s');
        }

        // targetAD
        $targetAD = $this->getApprovisionnementDetailByDate($produit, $dateApprovisionnement);
        // selection des approvisionnementDetails
        $approvisionneementDetails = $this->getApprovisionnementDetailsAfterPointDepart($produit, $dateDepart);
        // détermination des position de chaque approvisionnementDetail
        $cumul = 0;
        foreach ($approvisionneementDetails as $row) {
            $row->position = $cumul;
            if ($row->getId() == $targetAD->getId()) {
                $targetAD->position = $cumul;
            }
            $cumul += $row->getQuantite();
        }
        // selection des venteDetails
        $venteDetailsAssoc = $this->getVenteDetailsAfterPointDepart($produit, $dateVenteInitial);
        $quantiteInitiale = 0;
        $dateVente = (new DateTime())->format('Y-m-d H:i:s');

        $cumul = 0;
        if ($lastPointDepart) {
            $cumul += $lastPointDepart->getQuantiteInitiale();
        }
        for ($i = 0; $i < count($venteDetailsAssoc); $i++) {
            $row = $venteDetailsAssoc[$i];
            $row["position"] = $cumul;

            $cumul += $row['quantite'];

            if ($cumul >= $targetAD->position) {
                $quantiteInitiale = $cumul - $targetAD->position;
                $dateVente = $row['daty'];

                // je recherche ceux de même date
                $i++;
                while ($i < count($venteDetailsAssoc) && $venteDetailsAssoc[$i]['daty'] == $dateVente) {
                    $quantiteInitiale += $venteDetailsAssoc[$i]['quantite'];
                    $i++;
                }
                break;
            }
        }


        $newPointDepart = new PointDepart();
        $newPointDepart->setDateApprovisionnement(new Datetime($dateApprovisionnement));
        $newPointDepart->setDateVente(new DateTime($dateVente));
        $newPointDepart->setQuantiteInitiale($quantiteInitiale);
        $newPointDepart->setProduit($produit);

        $this->em->persist($newPointDepart);
        $this->em->flush();

        return true;
    }

    public function getPointDepart(Produit $produit)
    {
        $results = $this->em->getRepository(PointDepart::class)->createQueryBuilder('pointDepart')
            ->select()
            ->join('pointDepart.produit', 'produit')
            ->where('produit.id=:produitId')
            ->setParameter('produitId', $produit->getId())
            ->orderBy('pointDepart.dateApprovisionnement', 'desc')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if (!empty($results[0])) {
            return $results[0];
        }
        return null;
    }

    public function getQuantiteTotalVentes(Produit $produit, DateTime $dateDebut, DateTime $dateFin)
    {
        $sql = "select sum(quantite) as totalQuantite 
        from vente_detail 
        join vente on vente.id=vente_id
        where produit_id=" . $produit->getId() . "
        and vente.daty>='" . $dateDebut . "'
        and vente.daty<='" . $dateFin . "'";
    }

    public function getLastPrixAchat(Produit $produit)
    {
        $retour = 0;
        $dateDebut = new DateTime("1970-01-01");

        $dernierRepport = $this->stockService->getDernierRepportProduit($produit->getId(), $produit->getPointDeVente()->getId());
        if ($dernierRepport) {
            $dateDebut = $dernierRepport->getDaty();
            $retour = $dernierRepport->getPrixAchat();
        }

        // dernier approvisionnement detail
        $results = $this->em->getRepository(ApprovisionnementDetail::class)->createQueryBuilder('ad')
            ->select()
            ->join('ad.produit', 'produit')
            ->join('ad.approvisionnement', "approvisionnement")
            ->where('produit.id=:produitId')
            ->andWhere('approvisionnement.daty>=:dateDebut')
            ->setParameters(['dateDebut' => $dateDebut, "produitId" => $produit->getId()])
            ->addOrderBy('approvisionnement.daty', 'desc')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if (!empty($results[0])) {
            $retour = $results[0]->getPrixUnit();
        }

        return $retour;
    }

    public function getStatistiqueVente($produitId, $intervale, $dateDebut, $dateFin)
    {
        // dd($intervale);
        if ($intervale == "jour") {
            $sql = "select
            sum(quantite) as nbVentes,
            date(daty) as date
        from
            vente_detail
            join vente on vente.id = vente_id
            join produit on produit.id = produit_id
        where
            produit_id = " . $produitId .
                " and vente.daty >= '" . $dateDebut .
                "'and vente.daty <= '" . $dateFin .
                "'group by
            date";

            $results = $this->em->getConnection()->fetchAllAssociative($sql);
            return $results;
        } else if ($intervale == "mois") {
            $sql = "select
            sum(quantite) as nbVentes,
            concat(year(daty),'-',month(daty)) as date
        from
            vente_detail
            join vente on vente.id = vente_id
            join produit on produit.id = produit_id
        where
            produit_id = " . $produitId .
                " and vente.daty >= '" . $dateDebut .
                "'and vente.daty < '" . $dateFin .
                "'group by
            date";
            $results = $this->em->getConnection()->fetchAllAssociative($sql);
            return $results;
        } else if ($intervale == "année") {
            $sql = "select
            sum(quantite) as nbVentes,
            year(daty) as date
        from
            vente_detail
            join vente on vente.id = vente_id
            join produit on produit.id = produit_id
        where
            produit_id = " . $produitId .
                " and vente.daty >= '" . $dateDebut .
                "'and vente.daty < '" . $dateFin .
                "'group by
            date";
            $results = $this->em->getConnection()->fetchAllAssociative($sql);
            return $results;
        } else if ($intervale == "semaine") {
            $sql = "select
            sum(quantite) as nbVentes,
            concat(week(daty),'-',year(daty)) as date
        from
            vente_detail
            join vente on vente.id = vente_id
            join produit on produit.id = produit_id
        where
            produit_id = " . $produitId .
                " and vente.daty >= '" . $dateDebut .
                "'and vente.daty < '" . $dateFin .
                "'group by
            date order by vente.daty";
            $results = $this->em->getConnection()->fetchAllAssociative($sql);
            return $results;
        } else if ($intervale == "cette semaine") {
            $sql = "select
            sum(quantite) as nbVentes,
            date(daty) as date
        from
            vente_detail
            join vente on vente.id = vente_id
            join produit on produit.id = produit_id
        where
            produit_id = " . $produitId .
                " and vente.daty >= '" . $dateDebut .
                "'and vente.daty <= '" . $dateFin .
                "'group by
            date";
            $results = $this->em->getConnection()->fetchAllAssociative($sql);
            return $results;
        }
        throw new Exception("Undefined intervale");
    }

    public function getClassementProduitVente($pointDeVenteId)
    {
        $sql = "select produit.id,sum(quantite) as total_ventes from vente_detail join produit on produit_id=produit.id where produit.point_de_vente_id=" . $pointDeVenteId . " group by produit_id order by total_ventes desc limit 30";
        $results = $this->em->getConnection()->fetchAllAssociative($sql);
        $produitRepository = $this->em->getRepository(Produit::class);

        $retour = [];
        foreach ($results as $row) {
            $produit = $produitRepository->find($row["id"]);
            $normalized = $this->serializer->normalize($produit, null, ["groups" => ['produit:collection', 'categorie:collection', 'quantification:collection']]);
            $normalized["total_ventes"] = $row["total_ventes"];
            array_push($retour, $normalized);
        }

        return $retour;
    }

    public function getPrixTotalVentes($id)
    {
        $retour = 0;
        $produit = $this->em->getRepository(Produit::class)->find($id);
        if (empty($produit)) {
            throw new Exception("Le produit n'existe pas");
        }
        $pointDeVente = $produit->getPointDeVente();
        if (empty($pointDeVente)) {
            throw new Exception("Le produit n'a pas de point de vente");
        }
        $dateDebut = null;
        $repport = $this->stockService->getDernierRepportProduit($id, $pointDeVente->getId());
        if (!empty($repport)) {
            $dateDebut = $repport->getDaty();
        }

        $venteDetails = $this->approvisionnementService->getVenteDetailsProduit($id, $dateDebut);
        foreach ($venteDetails as $row) {
            $retour += $row->getPrix() * $row->getQuantite();
        }

        return $retour;
    }

    public function setApprovisionnementService(ApprovisionnementService $service)
    {
        $this->approvisionnementService = $service;
    }
    public function setStockService(StockService $service)
    {
        $this->stockService = $service;
    }

    public function deleteUnusedCategory()
    {
        $sql = "delete from
        categorie
            where
                id in (
                    select
                        categorie.id
                    from
                        categorie
                        left join produit_categorie on categorie_id = categorie.id
                    group by
                        categorie.id
                    having
                        count(categorie_id) = 0
            
            )";

        $this->em->getConnection()->executeQuery($sql);
    }

    public function getPrix($id)
    {
        $produit = $this->em->getRepository(Produit::class)->find($id);
        if (empty($produit)) {
            throw new Exception("Le produit n'existe pas");
        }
        $pointDeVente = $produit->getPointDeVente();
        if (empty($pointDeVente)) {
            throw new Exception("Le produit n'a pas de point de vente");
        }
        $retour = 0;
        $repport = $this->stockService->getDernierRepportProduit($id, $pointDeVente->getId());
        $dateDebut = null;


        if ($repport) {
            $retour = $repport->getPrixUnit();
            $dateDebut = $repport->getDaty();
            $nbVentes = $this->approvisionnementService->getQuantiteVenteProduit($id, $dateDebut);
            if ($nbVentes > $repport->getQuantite()) {
                $retour = 0;
            }
        }

        $approvisionnementDetails = $this->approvisionnementService->getApprovisionnementDetailsProduit($id, $dateDebut);
        // je prends juste le dernier
        $length = count($approvisionnementDetails);
        if ($length > 0) {
            $retour = $approvisionnementDetails[$length - 1]->getPrixVente();
        }

        if ($retour == 0) {
            $prix = $produit->getPrix();
            if ($prix) {
                $retour = $prix;
            }
        }

        return $retour;
    }

    // cette fonction est annulée temporairement
    // public function getPrix($id)
    // {
    //     $produit = $this->em->getRepository(Produit::class)->find($id);
    //     if (empty($produit)) {
    //         throw new Exception("Le produit n'existe pas");
    //     }
    //     $pointDeVente = $produit->getPointDeVente();
    //     if (empty($pointDeVente)) {
    //         throw new Exception("Le produit n'a pas de point de vente");
    //     }
    //     $retour = 0;
    //     $repport = $this->stockService->getDernierRepportProduit($id, $pointDeVente->getId());
    //     $dateDebut = null;


    //     $retour = $repport->getPrixUnit();
    //     if ($repport) {
    //         $dateDebut = $repport->getDaty();
    //         $nbVentes = $this->approvisionnementService->getQuantiteVenteProduit($id, $dateDebut);
    //         if ($nbVentes > $repport->getQuantite()) {
    //             $retour = 0;
    //         }
    //     }

    //     $approvisionnementDetails = $this->approvisionnementService->getApprovisionnementDetailsProduit($id, $dateDebut);
    //     foreach ($approvisionnementDetails as $row) {
    //         $quantiteRestante = $this->approvisionnementDetailService->quantiteRestante($row->getId());
    //         $prix = $row->getPrixVente();
    //         if ($quantiteRestante > 0 && $prix > $retour) {
    //             $retour = $prix;
    //         }
    //     }

    //     return $retour;
    // }

    // public function getQuantiteApprovisionnement(Produit $produit, $dateDebut, $dateFin, int $pointDeVenteId)
    // {
    //     $retour = 0;
    //     // quantification vérification
    //     $quantificationDefaut = $produit->getQuantification();
    //     if (!$quantificationDefaut) {
    //         throw new Exception("Le produit n'a aucune quantification par défaut", 500);
    //     }

    //     $query = $this->em->getRepository(ApprovisionnementDetail::class)
    //         ->createQueryBuilder('approvisionnement')
    //         ->select()
    //         ->join('approvisionnement.produit', 'produit')
    //         ->join('approvisionnement.approvisionnement', 'mere')
    //         ->join('mere.pointDeVente', 'pointDeVente')
    //         ->where('pointDeVente.id=:pointDeVenteId')
    //         ->andWhere('produit.id=:produitId')
    //         ->andWhere('approvisionnement.empty=false')
    //         ->setParameters([
    //             "pointDeVenteId" => $pointDeVenteId,
    //             "produitId" => $produit->getId()
    //         ]);

    //     if ($dateDebut != null) {
    //         $query->andWhere('mere.daty>=:dateDebut');
    //         $query->setParameter('dateDebut', $dateDebut);
    //     }
    //     if ($dateFin != null) {
    //         $query->andWhere('mere.daty<=:dateFin');
    //         $query->setParameter('dateFin', $dateFin);
    //     }

    //     $approvisionnements = $query
    //         ->getQuery()
    //         ->getResult();

    //     $normalized = $this->serializer->normalize($approvisionnements, null, ['groups' => ['approvisionnementDetail:collection', 'quantification:collection']]);

    //     // conversion de chaque quantification
    //     $quantificationEquivalences = $produit->getQuantificationEquivalences();
    //     foreach ($approvisionnements as $appro) {
    //         $quantite = $appro->getQuantite();
    //         $quantification = $appro->getQuantification();
    //         // quantification par defaut
    //         if ($quantification->getId() == $quantificationDefaut->getId()) {
    //             $retour += $quantite;
    //             continue;
    //         }
    //         // sinon, chercher quantitication equivalence
    //         $trouver = 0;
    //         foreach ($quantificationEquivalences as $equivalence) {
    //             if ($equivalence->getQuantification()->getId() == $quantification->getId()) {
    //                 // verification si la valeur existe
    //                 if (empty($equivalence->getValeur())) {
    //                     throw new Exception("Veuillez renseigner valeur de la quantification ‘" . $quantification->getNom() . "‘ dans le produit", 500);
    //                 }
    //                 $retour += $quantite / $equivalence->getValeur();
    //                 $trouver = 1;
    //                 break;
    //             }
    //         }
    //         if (!$trouver)
    //             throw new Exception("Veuillez renseigner la quantification ‘" . $quantification->getNom() . "‘ dans le produit", 500);
    //     }
    //     return ($retour);
    // }

    // public function getQuantiteVente(Produit $produit, $dateDebut, $dateFin, int $pointDeVenteId)
    // {
    //     $retour = 0;
    //     // quantification vérification
    //     $quantificationDefaut = $produit->getQuantification();
    //     if (!$quantificationDefaut) {
    //         throw new Exception("Le produit n'a aucune quantification par défaut", 500);
    //     }

    //     $query = $this->em->getRepository(VenteDetail::class)
    //         ->createQueryBuilder('venteDetail')
    //         ->select()
    //         ->join('venteDetail.produit', 'produit')
    //         ->join('venteDetail.vente', 'mere')
    //         ->join('mere.pointDeVente', 'pointDeVente')
    //         ->where('pointDeVente.id=:pointDeVenteId')
    //         ->andWhere('produit.id=:produitId')
    //         ->setParameters([
    //             "pointDeVenteId" => $pointDeVenteId,
    //             "produitId" => $produit->getId()
    //         ]);

    //     if ($dateDebut != null) {
    //         $query->andWhere('mere.daty>=:dateDebut');
    //         $query->setParameter('dateDebut', $dateDebut);
    //     }
    //     if ($dateFin != null) {
    //         $query->andWhere('mere.daty<=:dateFin');
    //         $query->setParameter('dateFin', $dateFin);
    //     }

    //     $venteDetails = $query
    //         ->getQuery()
    //         ->getResult();

    //     $quantificationEquivalences = $produit->getQuantificationEquivalences();
    //     foreach ($venteDetails as $detail) {
    //         $quantite = $detail->getQuantite();
    //         $quantification = $detail->getUnite();

    //         // si quantificationDefaut
    //         if ($quantification->getId() == $quantificationDefaut->getId()) {
    //             $retour += $quantite;
    //             continue;
    //         }
    //         // sinon chercher equivalence
    //         $trouver = 0;
    //         foreach ($quantificationEquivalences as $equivalence) {
    //             if ($equivalence->getQuantification()->getId() == $quantification->getId()) {
    //                 $trouver = 1;
    //                 $retour += $quantite / $equivalence->getValeur();
    //                 break;
    //             }
    //         }
    //         if (!$trouver) {
    //             throw new Exception("Veuillez renseigner la quantification ‘" . $quantification->getNom() . "‘ dans le produit", 500);
    //         }
    //     }

    //     $normalized = $this->serializer->normalize($venteDetails, null, ['groups' => ['venteDetail:collection', 'quantification:collection']]);

    //     return ($retour);
    // }

    public function getCurrentInventaire(Produit $produit, DateTime $date = new DateTime())
    {
        try {
            if (empty($produit)) {
                throw new Exception("Le produit n'existe pas", 500);
            }

            $results = $this->em->getRepository(RepportANouveau::class)->createQueryBuilder('repport')
                ->select()
                ->join('repport.produit', 'produit')
                ->where('produit.id=:produitId')
                ->andWhere('repport.daty <= :currentDate')
                ->setParameters([
                    'produitId' => $produit->getId(),
                    'currentDate' => $date
                ])
                ->addOrderBy('repport.daty', 'desc')
                ->setMaxResults(1)
                ->getQuery()
                ->getResult();

            if (!empty($results[0])) {
                return $results[0];
            }
            return null;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getApprovisionnementDetailsParDate(Produit $produit, Datetime $dateDebut, DateTime $dateFin)
    {


        $approvisionnements = $this->em->getRepository(ApprovisionnementDetail::class)
            ->createQueryBuilder('approvisionnement')
            ->select()
            ->join('approvisionnement.produit', 'produit')
            ->join('approvisionnement.approvisionnement', 'mere')
            ->join('mere.pointDeVente', 'pointDeVente')
            ->where('pointDeVente.id=:pointDeVenteId')
            ->andWhere('mere.daty<=:dateNow')
            ->andWhere('mere.daty>=:dateRepport')
            ->andWhere('produit.id=:produitId')
            ->setParameters([
                "pointDeVenteId" => $produit->getPointDeVente()->getId(),
                "dateNow" => $dateFin,
                "dateRepport" => $dateDebut,
                "produitId" => $produit->getId()
            ])
            ->addOrderBy('mere.daty', 'desc')
            ->getQuery()
            ->getResult();

        return $approvisionnements;
    }

    public function getVenteDetailsParDate(Produit $produit, DateTime $dateDebut, Datetime $dateFin)
    {

        $venteDetails = $this->em->getRepository(VenteDetail::class)
            ->createQueryBuilder('venteDetail')
            ->select()
            ->join('venteDetail.produit', 'produit')
            ->join('venteDetail.vente', 'mere')
            ->join('mere.pointDeVente', 'pointDeVente')
            ->where('pointDeVente.id=:pointDeVenteId')
            ->andWhere('mere.daty<=:dateNow')
            ->andWhere('mere.daty>=:dateRepport')
            ->andWhere('produit.id=:produitId')
            ->setParameters([
                "pointDeVenteId" => $produit->getPointDeVente()->getId(),
                "dateNow" => $dateFin,
                "dateRepport" => $dateDebut,
                "produitId" => $produit->getId()
            ])
            ->addOrderBy('mere.daty', 'desc')
            ->getQuery()
            ->getResult();

        return $venteDetails;
    }
}
