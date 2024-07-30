<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\NotificationRecouvrementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRecouvrementRepository::class)]
#[ApiResource()]
class NotificationRecouvrement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $daty = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateLecture = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDateLecture(): ?\DateTimeInterface
    {
        return $this->dateLecture;
    }

    public function setDateLecture(?\DateTimeInterface $dateLecture): static
    {
        $this->dateLecture = $dateLecture;

        return $this;
    }
}
