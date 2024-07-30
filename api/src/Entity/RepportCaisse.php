<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\RepportCaisseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RepportCaisseRepository::class)]
#[ApiResource]
class RepportCaisse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $daty = null;

    #[ORM\Column]
    private ?float $difference = null;

    #[ORM\ManyToOne(inversedBy: 'repportCaisses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PointDeVente $pointDeVente = null;

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

    public function getDaty(): ?\DateTimeInterface
    {
        return $this->daty;
    }

    public function setDaty(\DateTimeInterface $daty): static
    {
        $this->daty = $daty;

        return $this;
    }

    public function getDifference(): ?float
    {
        return $this->difference;
    }

    public function setDifference(float $difference): static
    {
        $this->difference = $difference;

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
}
