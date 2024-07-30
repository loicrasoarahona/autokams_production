<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use DateTime;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\VenteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: VenteRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiFilter(SearchFilter::class, properties: ['payed' => 'exact', "client.id" => "exact", "numFacture" => "partial", "isCommande" => "exact"])]
#[ApiFilter(OrderFilter::class, properties: ['daty', 'dateRecouvrement', 'venteLivraison.daty'], arguments: ['orderParameterName' => 'order'])]
#[UniqueEntity(fields: ['numFacture'], message: 'Ce numéro existe déjà')]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ["groups" => ["vente:post", "client:collection", "venteLivraison:collection"]]),
        new Post(
            denormalizationContext: ['groups' => ["vente:post", "client:collection", "venteDetail:collection", "approvisionnementDetail:collection", "paiement:collection", "venteLivraison:collection"]],
            normalizationContext: ['groups' => ["vente:post", "client:collection", "venteDetail:collection", "produit:collection", "quantification:collection"]]
        ),
        new Get(normalizationContext: ['groups' => ["vente:post", "client:collection", "venteDetail:collection", "venteLivraison:collection", "produit:collection", "paiement:collection"]]),
        new Put(denormalizationContext: ['groups' => ["vente:post", "client:collection", "venteDetail:collection", "venteLivraison:collection"]], normalizationContext: ['groups' => ["vente:post", "client:collection", "venteDetail:collection"]]),
        new Patch(),
        new Delete(),
    ],
)]
class Vente
{
    #[Groups(["vente:post"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(["vente:post"])]
    #[ORM\ManyToOne(inversedBy: 'ventes', cascade: ["persist"])]
    private ?Client $client = null;

    #[Groups(["vente:post"])]
    #[ORM\Column]
    private ?float $prix = null;

    #[Groups(["vente:post"])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[Groups(["vente:post"])]
    #[ORM\Column]
    private ?bool $payed = null;

    #[Groups(["vente:post"])]
    #[ORM\OneToMany(mappedBy: 'vente', targetEntity: Paiement::class, cascade: ["persist", "remove"])]
    private Collection $paiements;

    #[Groups(["vente:post"])]
    #[ORM\OneToMany(mappedBy: 'vente', targetEntity: VenteDetail::class, cascade: ["persist", "remove"])]
    private Collection $venteDetails;

    #[Groups(["vente:post"])]
    // #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d h:i:s'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $daty = null;

    #[Groups(["vente:post"])]
    #[ORM\ManyToOne(inversedBy: 'ventes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PointDeVente $pointDeVente = null;

    #[Groups(["vente:post"])]
    #[ORM\Column(nullable: true)]
    private ?int $numFacture = null;

    #[Groups(["vente:post"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateRecouvrement = null;

    #[Groups(["vente:post"])]
    #[ORM\Column(nullable: true)]
    private ?bool $isCommande = false;

    #[Groups(["vente:post"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[Groups(["vente:post"])]
    #[ORM\OneToMany(mappedBy: 'vente', targetEntity: VenteLivraison::class, cascade: ["persist", "remove"])]
    private Collection $venteLivraisons;


    public function __construct()
    {
        $this->paiements = new ArrayCollection();
        $this->venteDetails = new ArrayCollection();
        $this->venteLivraisons = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setDefaultDaty()
    {
        if (empty($this->daty))
            $this->daty = new DateTime();
    }

    #[ORM\PrePersist]
    public function setPrixTotal()
    {
        if (!$this->prix) {
            $total = 0;
            foreach ($this->getVenteDetails() as $detail) {
                $total += $detail->getPrix() * $detail->getQuantite();
            }
            $this->prix = $total;
        }
    }

    #[ORM\PreRemove]
    public function preRemove()
    {
    }

    #[Groups(["vente:recouvrement"])]
    public function getPaiementPercentage()
    {
        $prixTotal = 0;
        foreach ($this->getVenteDetails() as $detail) {
            $prixTotal += $detail->getPrix() * $detail->getQuantite();
        }
        // dd($prixTotal);

        $paiementTotal = 0;
        foreach ($this->getPaiements() as $paiement) {
            $paiementTotal += $paiement->getMontant();
        }

        if ($prixTotal == 0) return 0;
        return $paiementTotal * 100 / $prixTotal;
    }

    #[Groups(["vente:recouvrement"])]
    public function getPrixTotal()
    {
        $total = 0;
        foreach ($this->getVenteDetails() as $detail) {
            $total += $detail->getPrix() * $detail->getQuantite();
        }
        return $total;
    }

    #[Groups(["vente:recouvrement"])]
    public function getResteAPayer()
    {
        return $this->getPrixTotal() - $this->getTotalPaiements();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float | int $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function isPayed(): ?bool
    {
        return $this->payed;
    }

    public function setPayed(bool $payed): static
    {
        $this->payed = $payed;

        return $this;
    }

    /**
     * @return Collection<int, Paiement>
     */
    public function getPaiements(): Collection
    {
        return $this->paiements;
    }

    public function addPaiement(Paiement $paiement): static
    {
        if (!$this->paiements->contains($paiement)) {
            $this->paiements->add($paiement);
            $paiement->setVente($this);
        }

        return $this;
    }

    public function removePaiement(Paiement $paiement): static
    {
        if ($this->paiements->removeElement($paiement)) {
            // set the owning side to null (unless already changed)
            if ($paiement->getVente() === $this) {
                $paiement->setVente(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, VenteDetail>
     */
    public function getVenteDetails(): Collection
    {
        return $this->venteDetails;
    }

    public function addVenteDetail(VenteDetail $venteDetail): static
    {
        if (!$this->venteDetails->contains($venteDetail)) {
            $this->venteDetails->add($venteDetail);
            $venteDetail->setVente($this);
        }

        return $this;
    }

    public function removeVenteDetail(VenteDetail $venteDetail): static
    {
        if ($this->venteDetails->removeElement($venteDetail)) {
            // set the owning side to null (unless already changed)
            if ($venteDetail->getVente() === $this) {
                $venteDetail->setVente(null);
            }
        }

        return $this;
    }

    public function getDaty(): ?\DateTimeInterface
    {
        return $this->daty;
    }

    public function setDaty(\DateTimeInterface $daty): static
    {
        $this->daty = $daty;

        return $this;
    }

    public function getPointDeVente(): ?PointDeVente
    {
        return $this->pointDeVente;
    }

    #[Groups(["vente:post"])]
    public function getTotalPaiements(): float
    {
        $retour = 0;
        foreach ($this->getPaiements() as $paiement) {
            $retour += $paiement->getMontant();
        }
        return $retour;
    }

    public function setPointDeVente(?PointDeVente $pointDeVente): static
    {
        $this->pointDeVente = $pointDeVente;

        return $this;
    }

    public function getNumFacture(): ?int
    {
        return $this->numFacture;
    }

    public function setNumFacture(?int $numFacture): static
    {
        $this->numFacture = $numFacture;

        return $this;
    }

    public function getDateRecouvrement(): ?\DateTimeInterface
    {
        return $this->dateRecouvrement;
    }

    public function setDateRecouvrement(?\DateTimeInterface $dateRecouvrement): static
    {
        $this->dateRecouvrement = $dateRecouvrement;

        return $this;
    }

    public function isIsCommande(): ?bool
    {
        return $this->isCommande;
    }

    public function setIsCommande(?bool $isCommande): static
    {
        $this->isCommande = $isCommande;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, VenteLivraison>
     */
    public function getVenteLivraisons(): Collection
    {
        return $this->venteLivraisons;
    }

    public function addVenteLivraison(VenteLivraison $venteLivraison): static
    {
        if (!$this->venteLivraisons->contains($venteLivraison)) {
            $this->venteLivraisons->add($venteLivraison);
            $venteLivraison->setVente($this);
        }

        return $this;
    }

    public function removeVenteLivraison(VenteLivraison $venteLivraison): static
    {
        if ($this->venteLivraisons->removeElement($venteLivraison)) {
            // set the owning side to null (unless already changed)
            if ($venteLivraison->getVente() === $this) {
                $venteLivraison->setVente(null);
            }
        }

        return $this;
    }
}
