<?php

namespace App\Entity;

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
use App\Filter\OrSearchFilter;
use App\Repository\ProduitRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreRemove;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ApiResource(operations: [
    new GetCollection(normalizationContext: ['groups' => ['quantificationEquivalence:collection', 'produit:collection', 'categorie:collection', 'quantification:collection', 'pointDeVente:collection']]),
    new Post(),
    new Get(normalizationContext: ['groups' => ['produit:collection', 'quantificationEquivalence:collection', 'categorie:collection', 'quantification:collection']]),
    new Put(normalizationContext: ['groups' => ['produit:collection', 'categorie:collection', 'quantification:collection']]),
    new Patch(),
    new Delete(),
],)]
#[ApiFilter(SearchFilter::class, properties: ['nom' => 'partial', 'categorie.id' => 'exact', 'pointDeVente.id' => "exact", "reference" => "partial"])]
#[ApiFilter(OrderFilter::class, properties: ['nom', 'id'], arguments: ['orderParameterName' => 'order'])]
#[ApiFilter(OrSearchFilter::class, properties: ["search"])]
// #[UniqueEntity(fields: ['categorie', 'id'], message: 'Ce numéro existe déjà')]
class Produit
{
    #[Groups(['produit:collection'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['produit:collection'])]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Groups(['produit:collection'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Groups(['produit:collection'])]
    #[ORM\ManyToOne]
    private ?Quantification $quantification = null;

    #[Groups(['produit:collection'])]
    #[ORM\ManyToMany(targetEntity: Categorie::class, inversedBy: 'produits')]
    private Collection $categorie;



    #[Groups(['produit:collection'])]
    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: QuantificationEquivalence::class, cascade: ['remove', 'persist'])]
    private Collection $quantificationEquivalences;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: PrixUnite::class, orphanRemoval: true)]
    private Collection $prixUnites;

    #[Groups(['produit:collection'])]
    #[ORM\Column(nullable: true)]
    private ?float $prix = null;

    #[Groups(['produit:collection'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = "defaultProduit.jpg";

    #[Groups(['produit:collection'])]
    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PointDeVente $pointDeVente = null;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: VenteDetail::class)]
    private Collection $venteDetails;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: ApprovisionnementDetail::class)]
    private Collection $approvisionnementDetails;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: DifferenceStock::class)]
    private Collection $differenceStocks;

    #[Groups(['produit:collection'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\OneToMany(mappedBy: 'produit', targetEntity: RepportNouveau::class, orphanRemoval: true)]
    private Collection $repportNouveaus;

    public function __construct()
    {
        $this->categorie = new ArrayCollection();
        $this->quantificationEquivalences = new ArrayCollection();
        $this->prixUnites = new ArrayCollection();
        $this->differenceStocks = new ArrayCollection();
        $this->repportNouveaus = new ArrayCollection();
    }

    #[ORM\PreRemove]
    function setChildrenNull()
    {
        echo "remove";
        foreach ($this->venteDetails as $venteDetail) {
            $venteDetail->setProduit(null);
        }
        foreach ($this->approvisionnementDetails as $approvisionnementDetail) {
            $approvisionnementDetail->setProduit(null);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

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

    public function getQuantification(): ?Quantification
    {
        return $this->quantification;
    }

    public function setQuantification(?Quantification $quantification): static
    {
        $this->quantification = $quantification;

        return $this;
    }

    /**
     * @return Collection<int, Categorie>
     */
    public function getCategorie(): Collection
    {
        return $this->categorie;
    }

    public function addCategorie(Categorie $categorie): static
    {
        if (!$this->categorie->contains($categorie)) {
            $this->categorie->add($categorie);
        }

        return $this;
    }

    public function removeCategorie(Categorie $categorie): static
    {
        $this->categorie->removeElement($categorie);

        return $this;
    }


    /**
     * @return Collection<int, QuantificationEquivalence>
     */
    public function getQuantificationEquivalences(): Collection
    {
        return $this->quantificationEquivalences;
    }

    public function addQuantificationEquivalence(QuantificationEquivalence $quantificationEquivalence): static
    {
        if (!$this->quantificationEquivalences->contains($quantificationEquivalence)) {
            $this->quantificationEquivalences->add($quantificationEquivalence);
            $quantificationEquivalence->setProduit($this);
        }

        return $this;
    }

    public function removeQuantificationEquivalence(QuantificationEquivalence $quantificationEquivalence): static
    {
        if ($this->quantificationEquivalences->removeElement($quantificationEquivalence)) {
            // set the owning side to null (unless already changed)
            if ($quantificationEquivalence->getProduit() === $this) {
                $quantificationEquivalence->setProduit(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PrixUnite>
     */
    public function getPrixUnites(): Collection
    {
        return $this->prixUnites;
    }

    public function addPrixUnite(PrixUnite $prixUnite): static
    {
        if (!$this->prixUnites->contains($prixUnite)) {
            $this->prixUnites->add($prixUnite);
            $prixUnite->setProduit($this);
        }

        return $this;
    }

    public function removePrixUnite(PrixUnite $prixUnite): static
    {
        if ($this->prixUnites->removeElement($prixUnite)) {
            // set the owning side to null (unless already changed)
            if ($prixUnite->getProduit() === $this) {
                $prixUnite->setProduit(null);
            }
        }

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getImage(): ?string
    {
        if (!strlen($this->image)) {
            return "defaultProduit.jpg";
        }
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

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

    /**
     * @return Collection<int, DifferenceStock>
     */
    public function getDifferenceStocks(): Collection
    {
        return $this->differenceStocks;
    }

    public function addDifferenceStock(DifferenceStock $differenceStock): static
    {
        if (!$this->differenceStocks->contains($differenceStock)) {
            $this->differenceStocks->add($differenceStock);
            $differenceStock->setProduit($this);
        }

        return $this;
    }

    public function removeDifferenceStock(DifferenceStock $differenceStock): static
    {
        if ($this->differenceStocks->removeElement($differenceStock)) {
            // set the owning side to null (unless already changed)
            if ($differenceStock->getProduit() === $this) {
                $differenceStock->setProduit(null);
            }
        }

        return $this;
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

    /**
     * @return Collection<int, RepportNouveau>
     */
    public function getRepportNouveaus(): Collection
    {
        return $this->repportNouveaus;
    }

    public function addRepportNouveau(RepportNouveau $repportNouveau): static
    {
        if (!$this->repportNouveaus->contains($repportNouveau)) {
            $this->repportNouveaus->add($repportNouveau);
            $repportNouveau->setProduit($this);
        }

        return $this;
    }

    public function removeRepportNouveau(RepportNouveau $repportNouveau): static
    {
        if ($this->repportNouveaus->removeElement($repportNouveau)) {
            // set the owning side to null (unless already changed)
            if ($repportNouveau->getProduit() === $this) {
                $repportNouveau->setProduit(null);
            }
        }

        return $this;
    }
}
