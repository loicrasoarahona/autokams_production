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
use App\Repository\ApprovisionnementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ApprovisionnementRepository::class)]
#[ApiResource(operations: [
    new GetCollection(normalizationContext: ['groups' => ['approvisionnement:collection', 'fournisseur:collection']]),
    new Post(denormalizationContext: ['groups' => ['approvisionnementDetail:collection', 'approvisionnement:post', 'fournisseur:collection', 'pointDeVente:collection']]),
    new Get(normalizationContext: ['groups' => ['approvisionnementDetail:collection', 'produit:collection', 'quantification:collection', 'approvisionnement:collection', 'approvisionnement:post', 'fournisseur:collection', 'pointDeVente:collection']]),
    new Put(denormalizationContext: ['groups' => ['approvisionnementDetail:collection', 'approvisionnement:post', 'fournisseur:collection', 'pointDeVente:collection']]),
    new Patch(),
    new Delete(),
],)]
#[ApiFilter(DateFilter::class, properties: ['dateAchat'])]
#[ApiFilter(SearchFilter::class, properties: ['fournisseur.nom' => 'partial', 'fournisseur.id' => 'exact', 'pointDeVente.id' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['dateAchat', 'daty', 'fournisseur.nom', 'prixTotal', 'numFacture'], arguments: ['orderParameterName' => 'order'])]
class Approvisionnement
{
    #[Groups(['approvisionnement:post', 'approvisionnement:collection'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['approvisionnement:post', 'approvisionnement:collection'])]
    #[ORM\ManyToOne(cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?PointDeVente $pointDeVente = null;

    #[Groups(['approvisionnement:post', 'approvisionnement:collection'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $daty = null;

    #[Groups(['approvisionnement:post', 'approvisionnement:collection'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateAchat = null;

    #[Groups(['approvisionnement:post', 'approvisionnement:collection'])]
    #[ORM\ManyToOne(cascade: ["persist"])]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Fournisseur $fournisseur = null;

    #[Groups(['approvisionnement:post', 'approvisionnement:collection'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numFacture = null;

    #[Groups(['approvisionnement:post'])]
    #[ORM\OneToMany(mappedBy: 'approvisionnement', targetEntity: ApprovisionnementDetail::class, cascade: ["persist"])]
    private Collection $approvisionnementDetails;

    #[Groups(['approvisionnement:post', 'approvisionnement:collection'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Groups(['approvisionnement:post', 'approvisionnement:collection'])]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $datePaiement = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateCommande = null;

    #[ORM\OneToMany(mappedBy: 'approvisionnement', targetEntity: PaiementStock::class)]
    private Collection $paiementStocks;

    public function __construct()
    {
        $this->approvisionnementDetails = new ArrayCollection();
        $this->paiementStocks = new ArrayCollection();
    }

    #[Groups(['approvisionnement:collection'])]
    public function getPrixTotal()
    {
        $retour = 0;
        $details = $this->getApprovisionnementDetails();
        foreach ($details as $element) {
            $retour += $element->getPrixTotal();
        }
        return $retour;
    }

    #[Groups(['approvisionnement:collection'])]
    public function getTotalPaiements()
    {
        $somme = 0;
        foreach ($this->getPaiementStocks() as $paiementStock) {
            $somme += $paiementStock->getMontant();
        }
        return $somme;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDateAchat(): ?\DateTimeInterface
    {
        return $this->dateAchat;
    }

    public function setDateAchat(\DateTimeInterface $dateAchat): static
    {
        $this->dateAchat = $dateAchat;

        return $this;
    }

    public function getFournisseur(): ?Fournisseur
    {
        return $this->fournisseur;
    }

    public function setFournisseur(?Fournisseur $fournisseur): static
    {
        $this->fournisseur = $fournisseur;

        return $this;
    }

    public function getNumFacture(): ?string
    {
        return $this->numFacture;
    }

    public function setNumFacture(?string $numFacture): static
    {
        $this->numFacture = $numFacture;

        return $this;
    }

    /**
     * @return Collection<int, ApprovisionnementDetail>
     */
    public function getApprovisionnementDetails(): Collection
    {
        return $this->approvisionnementDetails;
    }

    public function addApprovisionnementDetail(ApprovisionnementDetail $approvisionnementDetail): static
    {
        if (!$this->approvisionnementDetails->contains($approvisionnementDetail)) {
            $this->approvisionnementDetails->add($approvisionnementDetail);
            $approvisionnementDetail->setApprovisionnement($this);
        }

        return $this;
    }

    public function removeApprovisionnementDetail(ApprovisionnementDetail $approvisionnementDetail): static
    {
        if ($this->approvisionnementDetails->removeElement($approvisionnementDetail)) {
            // set the owning side to null (unless already changed)
            if ($approvisionnementDetail->getApprovisionnement() === $this) {
                $approvisionnementDetail->setApprovisionnement(null);
            }
        }

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

    public function getDatePaiement(): ?\DateTimeInterface
    {
        return $this->datePaiement;
    }

    public function setDatePaiement(?\DateTimeInterface $datePaiement): static
    {
        $this->datePaiement = $datePaiement;

        return $this;
    }

    public function getDateCommande(): ?\DateTimeInterface
    {
        return $this->dateCommande;
    }

    public function setDateCommande(?\DateTimeInterface $dateCommande): static
    {
        $this->dateCommande = $dateCommande;

        return $this;
    }

    /**
     * @return Collection<int, PaiementStock>
     */
    public function getPaiementStocks(): Collection
    {
        return $this->paiementStocks;
    }

    public function addPaiementStock(PaiementStock $paiementStock): static
    {
        if (!$this->paiementStocks->contains($paiementStock)) {
            $this->paiementStocks->add($paiementStock);
            $paiementStock->setApprovisionnement($this);
        }

        return $this;
    }

    public function removePaiementStock(PaiementStock $paiementStock): static
    {
        if ($this->paiementStocks->removeElement($paiementStock)) {
            // set the owning side to null (unless already changed)
            if ($paiementStock->getApprovisionnement() === $this) {
                $paiementStock->setApprovisionnement(null);
            }
        }

        return $this;
    }
}
