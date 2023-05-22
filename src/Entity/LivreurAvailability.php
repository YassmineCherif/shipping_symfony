<?php

namespace App\Entity;

use App\Repository\LivreurAvailabilityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LivreurAvailabilityRepository::class)]
class LivreurAvailability
{
    #[ORM\ManyToOne(targetEntity: Livreur::class, inversedBy: "livreurAvailabilities")]
    #[ORM\JoinColumn(name: "id_livreur", referencedColumnName: "id", nullable: false)]
    private ?Livreur $livreur = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $id_livreur = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reason = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdLivreur(): ?int
    {
        return $this->id_livreur;
    }

    public function setIdLivreur(int $id_livreur): self
    {
        $this->id_livreur = $id_livreur;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }
    public function getLivreur(): ?Livreur
    {
        return $this->livreur;
    }

    public function setLivreur(?Livreur $livreur): self
    {
        $this->livreur = $livreur;

        return $this;
    }
}
