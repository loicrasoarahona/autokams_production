<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\RepportNouveauRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RepportNouveauRepository::class)]
#[ApiResource]
class RepportNouveau
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'repportNouveaus')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    #[ORM\Column]
    private ?float $quantite = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quantification $quantification = null;

    #[ORM\Column]
    private ?float $prixAchat = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $daty = null;

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

    public function getPrixAchat(): ?float
    {
        return $this->prixAchat;
    }

    public function setPrixAchat(float $prixAchat): static
    {
        $this->prixAchat = $prixAchat;

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
}
