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
use App\Repository\PaiementStockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PaiementStockRepository::class)]
#[ApiFilter(SearchFilter::class, properties: ['approvisionnement.id' => 'exact'])]
#[ApiResource(operations: [
    new GetCollection(normalizationContext: ['groups' => ['paiementStock:collection', 'moyenPaiement:collection']]),
    new Get(normalizationContext: ['groups' => ['paiementStock:collection', 'approvisionnement:collection', 'moyenPaiement:collection']]),
    new Post(),
    new Put(),
    new Delete(),
    new Patch(),
])]
class PaiementStock
{

    #[Groups(['paiementStock:collection'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['paiementStock:collection'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $daty = null;

    #[Groups(['paiementStock:collection'])]
    #[ORM\Column]
    private ?float $montant = null;

    #[Groups(['paiementStock:collection'])]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?MoyenPaiement $moyenPaiement = null;

    #[Groups(['paiementStock:collection'])]
    #[ORM\ManyToOne(inversedBy: 'paiementStocks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Approvisionnement $approvisionnement = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getMoyenPaiement(): ?MoyenPaiement
    {
        return $this->moyenPaiement;
    }

    public function setMoyenPaiement(?MoyenPaiement $moyenPaiement): static
    {
        $this->moyenPaiement = $moyenPaiement;

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
}
