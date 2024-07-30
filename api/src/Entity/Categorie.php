<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
#[ApiResource(operations: [
    new GetCollection(normalizationContext: ['groups' => 'categorie:collection']),
    new Post(),
    new Get(),
    new Put(),
    new Patch(),
    new Delete(),
],)]
#[ApiFilter(SearchFilter::class, properties: ['nom' => 'partial'])]
class Categorie
{
    #[Groups(['categorie:collection'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[NotBlank()]
    #[Groups(['categorie:collection'])]
    #[ORM\Column(length: 255, nullable: false)]
    private ?string $nom = null;

    #[Groups(['categorie:collection'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Groups(['categorie:collection'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[Groups(['categorie:collection'])]
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'categories')]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?self $mere = null;

    #[ORM\OneToMany(mappedBy: 'mere', targetEntity: self::class)]
    private Collection $categories;

    #[ORM\ManyToMany(targetEntity: Produit::class, mappedBy: 'categorie')]
    private Collection $produits;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->produits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getMere(): ?self
    {
        return $this->mere;
    }

    public function setMere(?self $mere): static
    {
        $this->mere = $mere;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(self $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
            $category->setMere($this);
        }

        return $this;
    }

    public function removeCategory(self $category): static
    {
        if ($this->categories->removeElement($category)) {
            // set the owning side to null (unless already changed)
            if ($category->getMere() === $this) {
                $category->setMere(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): static
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->addCategorie($this);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): static
    {
        if ($this->produits->removeElement($produit)) {
            $produit->removeCategorie($this);
        }

        return $this;
    }
}
