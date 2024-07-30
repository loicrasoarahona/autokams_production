<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\VenteDetailApprovisionnementDetailRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VenteDetailApprovisionnementDetailRepository::class)]
#[ApiResource]
class VenteDetailApprovisionnementDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?ApprovisionnementDetail $approvisionnementDetail = null;

    #[ORM\Column]
    private ?float $quantite = null;

    #[ORM\ManyToOne(inversedBy: 'venteDetailApprovisionnementDetails')]
    #[ORM\JoinColumn(nullable: false)]
    private ?VenteDetail $venteDetail = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getApprovisionnementDetail(): ?ApprovisionnementDetail
    {
        return $this->approvisionnementDetail;
    }

    public function setApprovisionnementDetail(?ApprovisionnementDetail $approvisionnementDetail): static
    {
        $this->approvisionnementDetail = $approvisionnementDetail;

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

    public function getVenteDetail(): ?VenteDetail
    {
        return $this->venteDetail;
    }

    public function setVenteDetail(?VenteDetail $venteDetail): static
    {
        $this->venteDetail = $venteDetail;

        return $this;
    }
}
