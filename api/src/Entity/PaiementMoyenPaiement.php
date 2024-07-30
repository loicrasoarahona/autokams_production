<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PaiementMoyenPaiementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PaiementMoyenPaiementRepository::class)]
#[ApiResource]
class PaiementMoyenPaiement
{
    #[Groups(["paiement:collection"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(["paiement:collection"])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reference = null;

    #[Groups(["paiement:collection"])]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?MoyenPaiement $moyenPaiement = null;

    #[Groups(["paiement:collection"])]
    #[ORM\OneToOne(inversedBy: 'paiementMoyenPaiement', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Paiement $paiement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;

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

    public function getPaiement(): ?Paiement
    {
        return $this->paiement;
    }

    public function setPaiement(Paiement $paiement): static
    {
        $this->paiement = $paiement;

        return $this;
    }
}
