<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\VenteLivraisonDetailRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: VenteLivraisonDetailRepository::class)]
#[ApiResource]
class VenteLivraisonDetail
{
    #[Groups(["vente:post"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(["vente:post"])]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    #[Groups(["vente:post"])]
    #[ORM\Column]
    private ?float $quantite = null;

    #[Groups(["vente:post"])]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quantification $quantification = null;

    #[Groups(["vente:post"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $quantiteStr = null;

    #[ORM\ManyToOne(inversedBy: 'venteLivraisonDetails')]
    #[ORM\JoinColumn(nullable: false)]
    private ?VenteLivraison $venteLivraison = null;

    #[ORM\Column]
    private ?bool $livrer = false;

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

    public function getQuantiteStr(): ?string
    {
        return $this->quantiteStr;
    }

    public function setQuantiteStr(?string $quantiteStr): static
    {
        $this->quantiteStr = $quantiteStr;

        return $this;
    }

    public function getVenteLivraison(): ?VenteLivraison
    {
        return $this->venteLivraison;
    }

    public function setVenteLivraison(?VenteLivraison $venteLivraison): static
    {
        $this->venteLivraison = $venteLivraison;

        return $this;
    }

    public function isLivrer(): ?bool
    {
        return $this->livrer;
    }

    public function setLivrer(bool $livrer): static
    {
        $this->livrer = $livrer;

        return $this;
    }
}
