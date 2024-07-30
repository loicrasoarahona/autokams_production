<?php

namespace App\Entity;

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
use App\Repository\RepportANouveauRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RepportANouveauRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ["groups" => ["repport:collection", "produit:collection", "quantification:collection"]]),
        new Post(),
        new Get(normalizationContext: ["groups" => ["repport:collection", "produit:collection", "quantification:collection"]]),
        new Put(),
        new Patch(),
        new Delete(),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: ['produit.id' => 'exact', "pointDeVente.id" => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['daty'], arguments: ['orderParameterName' => 'order'])]
class RepportANouveau
{
    #[Groups(['repport:collection'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['repport:collection', "stockVente"])]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Produit $produit = null;

    #[Groups(['repport:collection', "stockVente"])]
    #[ORM\Column]
    private ?float $quantite = null;

    #[Groups(['repport:collection', "stockVente"])]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quantification $quantification = null;

    #[Groups(['repport:collection', "stockVente"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $daty = null;

    #[Groups(['repport:collection'])]
    #[ORM\ManyToOne(inversedBy: 'repportANouveaus')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PointDeVente $pointDeVente = null;

    #[Groups(['repport:collection'])]
    #[ORM\Column(nullable: true)]
    private ?float $prixUnit = null;

    #[Groups(['repport:collection'])]
    #[ORM\Column(nullable: true)]
    private ?float $prixAchat = null;

    #[Groups(['stockVente'])]
    public function getRepportId()
    {
        return $this->id;
    }

    #[Groups(['stockVente'])]
    public function getPrixVente()
    {
        return $this->prixUnit;
    }

    #[ORM\PrePersist]
    public function setDefaultPointDeVente()
    {
        if (!$this->pointDeVente) {
            $this->pointDeVente = $this->produit->getPointDeVente();
        }
    }

    #[ORM\PrePersist]
    public function setDefaultDaty()
    {
        if (!$this->daty) {
            $this->daty = new DateTime();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(float $quantite): static
    {
        $this->quantite = $quantite;

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

    public function setPointDeVente(?PointDeVente $pointDeVente): static
    {
        $this->pointDeVente = $pointDeVente;

        return $this;
    }

    public function getPrixUnit(): ?float
    {
        return $this->prixUnit;
    }

    public function setPrixUnit(?float $prixUnit): static
    {
        $this->prixUnit = $prixUnit;

        return $this;
    }

    public function getPrixAchat(): ?float
    {
        return $this->prixAchat;
    }

    public function setPrixAchat(?float $prixAchat): static
    {
        $this->prixAchat = $prixAchat;

        return $this;
    }
}
