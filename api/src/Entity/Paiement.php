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
use App\Repository\PaiementRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PaiementRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(operations: [
    new GetCollection(normalizationContext: ['groups' => ["paiement:collection", "vente:post", "client:collection"]]),
    new Post(),
    new Get(),
    new Put(),
    new Patch(),
    new Delete(),
],)]
#[ApiFilter(OrderFilter::class, properties: ['date'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(SearchFilter::class, properties: ['vente.id' => 'exact', 'vente.client.nom' => 'partial', 'vente.pointDeVente.id' => "exact"])]
#[ApiFilter(DateFilter::class, properties: ['date'])]
class Paiement
{
    #[Groups(["paiement:collection"])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(["paiement:collection"])]
    #[ORM\ManyToOne(inversedBy: 'paiements', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vente $vente = null;

    #[Groups(["paiement:collection"])]
    #[ORM\Column]
    private ?float $montant = null;

    #[Groups(["paiement:collection"])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[Groups(["paiement:collection"])]
    #[ORM\OneToOne(mappedBy: 'paiement', cascade: ['persist', 'remove'])]
    private ?PaiementMoyenPaiement $paiementMoyenPaiement = null;

    #[ORM\ManyToOne(inversedBy: 'paiements')]
    private ?Versement $versement = null;

    public function __construct()
    {
    }

    #[ORM\PrePersist]
    public function setDefaultDaty()
    {
        if (!$this->date)
            $this->date = new DateTime();
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

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): static
    {
        $this->montant = $montant;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getPaiementMoyenPaiement(): ?PaiementMoyenPaiement
    {
        return $this->paiementMoyenPaiement;
    }

    public function setPaiementMoyenPaiement(PaiementMoyenPaiement $paiementMoyenPaiement): static
    {
        // set the owning side of the relation if necessary
        if ($paiementMoyenPaiement->getPaiement() !== $this) {
            $paiementMoyenPaiement->setPaiement($this);
        }

        $this->paiementMoyenPaiement = $paiementMoyenPaiement;

        return $this;
    }

    public function getVersement(): ?Versement
    {
        return $this->versement;
    }

    public function setVersement(?Versement $versement): static
    {
        $this->versement = $versement;

        return $this;
    }
}
