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
use App\Repository\QuantificationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: QuantificationRepository::class)]
#[ApiResource(operations: [
    new GetCollection(normalizationContext: ['groups' => 'quantification:collection']),
    new Post(),
    new Get(),
    new Put(),
    new Patch(),
    new Delete(),
],)]
#[UniqueEntity(fields: ['nom'], message: 'ce nom existe déjà')]
#[UniqueEntity(fields: ['symbole'], message: 'ce symbole existe déjà')]
#[ApiFilter(SearchFilter::class, properties: ['nom' => 'partial', 'symbole' => 'partial'])]
#[ApiFilter(OrderFilter::class, properties: ['nom', 'symbole'], arguments: ['orderParameterName' => 'order'])]
class Quantification
{
    #[Groups(['quantification:collection'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['quantification:collection'])]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Groups(['quantification:collection'])]
    #[ORM\Column(length: 255)]
    private ?string $symbole = null;




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

    public function getSymbole(): ?string
    {
        return $this->symbole;
    }

    public function setSymbole(string $symbole): static
    {
        $this->symbole = $symbole;

        return $this;
    }
}
