<?php

namespace App\Entity;

use App\Repository\MoyenDeTransportRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: MoyenDeTransportRepository::class)]
#[ORM\Table(name: "moyen_de_transport")]
class MoyenDeTransport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $marque;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Assert\Choice(choices: [0, 1], message: "Le type doit être soit 0 pour 'vélo' ou 1 pour 'voiture'")]
    private $type;

    #[ORM\Column(name: "Matricule", type: "string", length: 10, nullable: true)]
    private $matricule;

    #[ORM\ManyToOne(targetEntity: Partenaire::class)]
    #[ORM\JoinColumn(nullable: false, name: "id_par", referencedColumnName: "id")]
    private $partner;

    #[ORM\ManyToOne(targetEntity: Livreur::class, inversedBy: "moyensDeTransport")]
    #[ORM\JoinColumn(nullable: true, name: "livreur_id", onDelete: "SET NULL",referencedColumnName: "id")]
    private $livreur;

    public function getLivreur(): ?Livreur
    {
        return $this->livreur;
    }


    public function setLivreur(?Livreur $livreur): self
    {
        $this->livreur = $livreur;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMarque(): ?string
    {
        return $this->marque;
    }

    public function setMarque(?string $marque): self
    {
        $this->marque = $marque;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(?string $matricule): self
    {
        $this->matricule = $matricule;

        return $this;
    }

    public function getPartner(): ?Partenaire
    {
        return $this->partner;
    }


    public function SetPartner(?Partenaire $partner): self
    {
        $this->partner = $partner;

        return $this;
    }


}