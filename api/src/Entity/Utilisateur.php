<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\UtilisateurRepository;
use App\State\UserPasswordHasher;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[UniqueEntity(fields: ['pseudo'], message: 'cet utilisateur existe déjà')]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['user:collection', 'pointDeVente:collection']]),
        new Post(processor: UserPasswordHasher::class, validationContext: ['groups' => ['Default', 'user:create']]),
        new Get(normalizationContext: ['groups' => ['Default', 'user:collection', 'pointDeVente:collection']]),
        new Put(processor: UserPasswordHasher::class, validationContext: ['groups' => ['Default', 'user:create']]),
        new Patch(processor: UserPasswordHasher::class),
        new Delete(),
    ],
    // normalizationContext: ['groups' => ['user:collection']],
    // denormalizationContext: ['groups' => ['user:create', 'user:update']],
)]
class Utilisateur implements PasswordAuthenticatedUserInterface, UserInterface
{
    #[Groups(['user:collection', 'user:collection'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['user:collection', 'user:collection', 'user:create', 'user:update'])]
    #[ORM\Column(length: 255)]
    private ?string $pseudo = null;

    #[ORM\Column(length: 255)]
    private ?string $mdp = null;

    #[Groups(['user:collection', 'user:create'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nom = null;

    #[Groups(['user:collection', 'user:create'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prenom = null;

    #[Groups(['user:collection', 'user:create'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[Groups(['user:create'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $plainPassword = null;

    #[Groups(['user:collection'])]
    #[ORM\ManyToOne(inversedBy: 'utilisateurs')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PointDeVente $pointDeVente = null;

    #[Groups(['user:collection'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $role = null;

    public function getPassword(): ?string
    {
        return $this->mdp;
    }

    public function getUserIdentifier(): string
    {
        return $this->getPseudo();
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
        return;
    }

    public function getRoles(): array
    {
        return [$this->role];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getMdp(): ?string
    {
        return $this->mdp;
    }

    public function setMdp(string $mdp): static
    {
        $this->mdp = $mdp;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): static
    {
        $this->prenom = $prenom;

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

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;

        return $this;
    }
}
