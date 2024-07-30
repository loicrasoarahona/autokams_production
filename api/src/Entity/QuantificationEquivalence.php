<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\QuantificationEquivalenceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: QuantificationEquivalenceRepository::class)]
#[ApiResource(operations: [
    new GetCollection(normalizationContext: ['groups' => ['quantificationEquivalence:collection', 'quantification:collection']]),
    new Post(),
    new Get(),
    new Put(),
    new Patch(),
    new Delete(),
],)]
#[ApiFilter(SearchFilter::class, properties: ['produit' => 'exact', 'produit.id' => 'exact', 'quantification.nom' => 'partial', 'quantification.id' => 'exact'])]
#[UniqueEntity(fields: ['produit', 'quantification'], message: 'Cette quantification existe déjà pour ce produit.')]
class QuantificationEquivalence
{
    #[Groups(['quantificationEquivalence:collection'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['quantificationEquivalence:collection'])]
    #[ORM\ManyToOne(inversedBy: 'quantificationEquivalences')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    #[Groups(['quantificationEquivalence:collection'])]
    #[ORM\Column(nullable: true)]
    private ?float $valeur = null;

    #[Groups(['quantificationEquivalence:collection'])]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quantification $quantification = null;

    #[Groups(['quantificationEquivalence:collection'])]
    #[ORM\Column(nullable: true)]
    private ?float $prix = null;

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

    public function getValeur(): ?float
    {
        return $this->valeur;
    }

    public function setValeur(?float $valeur): static
    {
        $this->valeur = $valeur;

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

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }
}
