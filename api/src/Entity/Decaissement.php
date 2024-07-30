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
use App\Repository\DecaissementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DecaissementRepository::class)]
#[ApiResource(operations: [
    new GetCollection(normalizationContext: ['groups' => ['decaissement:collection', 'decaisseur:collection']]),
    new Post(denormalizationContext: ['groups' => ['decaissement:collection', 'decaisseur:collection']]),
    new Get(normalizationContext: ['groups' => ['decaissement:collection', 'decaisseur:collection']]),
    new Put(denormalizationContext: ['groups' => ['decaissement:collection', 'decaisseur:collection']]),
    new Patch(),
    new Delete(),
])]
#[ApiFilter(OrderFilter::class, properties: ['daty'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(DateFilter::class, properties: ['daty'])]
class Decaissement
{
    #[Groups(['decaissement:collection'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['decaissement:collection'])]
    #[ORM\Column]
    private ?float $montant = null;

    #[Groups(['decaissement:collection'])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[Groups(['decaissement:collection'])]
    #[ORM\ManyToOne(inversedBy: 'decaissements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PointDeVente $pointDeVente = null;

    #[Groups(['decaissement:collection'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $daty = null;

    #[Groups(['decaissement:collection'])]
    #[ORM\ManyToOne(inversedBy: 'decaissements', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Decaisseur $responsable = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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

    public function getDaty(): ?\DateTimeInterface
    {
        return $this->daty;
    }

    public function setDaty(\DateTimeInterface $daty): static
    {
        $this->daty = $daty;

        return $this;
    }

    public function getResponsable(): ?Decaisseur
    {
        return $this->responsable;
    }

    public function setResponsable(?Decaisseur $responsable): static
    {
        $this->responsable = $responsable;

        return $this;
    }
}
