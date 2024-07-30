<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\FournisseurRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: FournisseurRepository::class)]
#[ApiResource]
#[ApiFilter(SearchFilter::class, properties: ['nom' => 'partial'])]
class Fournisseur
{
    #[Groups(['fournisseur:collection'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['fournisseur:collection'])]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Groups(['fournisseur:collection'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    #[Groups(['fournisseur:collection'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[Groups(['fournisseur:collection'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adresse = null;

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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }
}
