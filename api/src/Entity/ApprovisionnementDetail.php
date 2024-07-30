<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\ApprovisionnementDetailRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ApprovisionnementDetailRepository::class)]
#[ApiResource(operations: [
    new GetCollection(normalizationContext: ['groups' => ['approvisionnementDetail:collection', 'approvisionnement:collection', 'quantification:collection', 'fournisseur:collection']]),
    new Post(
        denormalizationContext: ['groups' => ['approvisionnementDetail:collection']],
        normalizationContext: ["groups" => ['approvisionnementDetail:collection', 'approvisionnement:collection', 'quantification:collection', 'fournisseur:collection']]
    ),
    new Get(normalizationContext: ["groups" => ['approvisionnementDetail:collection', 'approvisionnement:collection', 'quantification:collection', 'stockVente', 'produit:collection']]),
    new Put(
        denormalizationContext: ["groups" => ['approvisionnementDetail:collection', 'approvisionnement:collection']],
        normalizationContext: ['groups' => ['approvisionnementDetail:collection']]
    ),
    new Patch(),
    new Delete(),
],)]
#[ApiFilter(SearchFilter::class, properties: ['produit.id' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['approvisionnement.dateAchat', 'approvisionnement.daty'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(DateFilter::class, properties: ['approvisionnement.daty'])]
class ApprovisionnementDetail
{
    #[Groups(["approvisionnementDetail:collection", "stockVente"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(["approvisionnementDetail:collection", "stockVente"])]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Produit $produit = null;

    #[Groups(["approvisionnementDetail:collection"])]
    #[ORM\Column]
    private ?float $prixUnit = null;

    #[Groups(["approvisionnementDetail:collection", "stockVente"])]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Quantification $quantification = null;

    #[Groups(["approvisionnementDetail:collection", "stockVente"])]
    #[ORM\Column]
    private ?float $quantite = null;

    #[Groups(["approvisionnementDetail:collection"])]
    #[ORM\ManyToOne(inversedBy: 'approvisionnementDetails', cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Approvisionnement $approvisionnement = null;

    #[Groups(["approvisionnementDetail:collection"])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'approvisionnementDetail', targetEntity: VenteDetail::class)]
    private Collection $venteDetails;

    #[Groups(["approvisionnementDetail:collection", "stockVente"])]
    #[ORM\Column(nullable: true)]
    private ?float $prixVente = null;

    #[Groups(["approvisionnementDetail:collection"])]
    #[ORM\Column]
    private ?bool $empty = false;


    #[Groups(["stockVente"])]
    public function getDaty()
    {
        return $this->getApprovisionnement()->getDaty();
    }

    public function __construct()
    {
        $this->venteDetails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    #[Groups(['approvisionnementDetail:collection'])]
    public function getPrixTotal()
    {
        return $this->getPrixUnit() * $this->getQuantite();
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;

        return $this;
    }

    public function getPrixUnit(): ?float
    {
        return $this->prixUnit;
    }

    public function setPrixUnit(float $prixUnit): static
    {
        $this->prixUnit = $prixUnit;

        return $this;
    }

    public function getQuantification(): ?Quantification
    {
        return $this->quantification;
    }

    public function setQuantification(?Quantification $quantification): static
    {
        $this->quantification = $quantification;

        return $this;
    }

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(float $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getApprovisionnement(): ?Approvisionnement
    {
        return $this->approvisionnement;
    }

    public function setApprovisionnement(?Approvisionnement $approvisionnement): static
    {
        $this->approvisionnement = $approvisionnement;

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
     * @return Collection<int, VenteDetail>
     */
    public function getVenteDetails(): Collection
    {
        return $this->venteDetails;
    }

    public function getPrixVente(): ?float
    {
        return $this->prixVente;
    }

    public function setPrixVente(?float $prixVente): static
    {
        $this->prixVente = $prixVente;

        return $this;
    }

    public function isEmpty(): ?bool
    {
        return $this->empty;
    }

    public function setEmpty(bool $empty): static
    {
        $this->empty = $empty;

        return $this;
    }
}
