<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\PointDepartRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PointDepartRepository::class)]
#[ApiResource]
#[ApiFilter(SearchFilter::class, properties: ['produit.id' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['dateApprovisionnement', 'dateVente'], arguments: ['orderParameterName' => 'order'])]
class PointDepart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateApprovisionnement = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateVente = null;

    #[ORM\Column]
    private ?float $quantiteInitiale = null;

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

    public function getDateApprovisionnement(): ?\DateTimeInterface
    {
        return $this->dateApprovisionnement;
    }

    public function setDateApprovisionnement(\DateTimeInterface $dateApprovisionnement): static
    {
        $this->dateApprovisionnement = $dateApprovisionnement;

        return $this;
    }

    public function getDateVente(): ?\DateTimeInterface
    {
        return $this->dateVente;
    }

    public function setDateVente(\DateTimeInterface $dateVente): static
    {
        $this->dateVente = $dateVente;

        return $this;
    }

    public function getQuantiteInitiale(): ?float
    {
        return $this->quantiteInitiale;
    }

    public function setQuantiteInitiale(float $quantiteInitiale): static
    {
        $this->quantiteInitiale = $quantiteInitiale;

        return $this;
    }
}
