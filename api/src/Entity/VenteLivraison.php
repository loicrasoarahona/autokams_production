<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\VenteLivraisonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: VenteLivraisonRepository::class)]
#[ApiResource]
class VenteLivraison
{

    #[Groups(["vente:post"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(["vente:post"])]
    #[ORM\ManyToOne(inversedBy: 'venteLivraisons')]
    private ?Vente $vente = null;

    #[Groups(["vente:post"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $daty = null;

    #[Groups(["vente:post"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[Groups(["vente:post"])]
    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[Groups(["vente:post"])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[Groups(["vente:post"])]
    #[ORM\OneToMany(mappedBy: 'venteLivraison', targetEntity: VenteLivraisonDetail::class, orphanRemoval: true, cascade: ["persist"])]
    private Collection $venteLivraisonDetails;

    public function __construct()
    {
        $this->venteLivraisonDetails = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVente(): ?Vente
    {
        return $this->vente;
    }

    public function setVente(?Vente $vente): static
    {
        $this->vente = $vente;

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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

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
     * @return Collection<int, VenteLivraisonDetail>
     */
    public function getVenteLivraisonDetails(): Collection
    {
        return $this->venteLivraisonDetails;
    }

    public function addVenteLivraisonDetail(VenteLivraisonDetail $venteLivraisonDetail): static
    {
        if (!$this->venteLivraisonDetails->contains($venteLivraisonDetail)) {
            $this->venteLivraisonDetails->add($venteLivraisonDetail);
            $venteLivraisonDetail->setVenteLivraison($this);
        }

        return $this;
    }

    public function removeVenteLivraisonDetail(VenteLivraisonDetail $venteLivraisonDetail): static
    {
        if ($this->venteLivraisonDetails->removeElement($venteLivraisonDetail)) {
            // set the owning side to null (unless already changed)
            if ($venteLivraisonDetail->getVenteLivraison() === $this) {
                $venteLivraisonDetail->setVenteLivraison(null);
            }
        }

        return $this;
    }
}
