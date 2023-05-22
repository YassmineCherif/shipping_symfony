<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ColisRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ColisRepository::class)]
class Colis
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $ref = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Range(
        min: 1,
        max: 180,
        notInRangeMessage: 'Votre colis doit avoir entre {{ min }}cm et {{ max }}cm',
    )]
    private ?int $hauteur = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Range(
        min: 1,
        max: 180,
        notInRangeMessage: 'Votre colis doit avoir entre {{ min }}cm et {{ max }}cm',
    )]
    private ?int $largeur = null;

    #[ORM\Column]
    #[Assert\Positive]
    #[Assert\NotBlank]
    private ?int $poids = null;

    #[ORM\Column]
    private ?int $prix = null;

    #[ORM\Column]
    private ?bool $fragile = null;

    #[ORM\Column]
    private ?bool $inflammable = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $depart = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $destination = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $etat_colis = null;

    #[ORM\Column(length: 255)]
    private ?string $zone = null;

    #[ORM\Column]
    private ?bool $urgent = null;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Client", inversedBy: "colis")]
    #[ORM\JoinColumn(name: "id_client", referencedColumnName: "id")]
    private $id_client;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Livreur", inversedBy: "colis")]
    #[ORM\JoinColumn(name: "id_livreur", referencedColumnName: "id")]
    private $livreur;

    #[ORM\ManyToOne(targetEntity: "App\Entity\Partenaire", inversedBy: "colis")]
    #[ORM\JoinColumn(name: "id_partenaire", referencedColumnName: "id")]
    private $id_partenaire;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): self
    {
        $this->ref = $ref;

        return $this;
    }

    public function getHauteur(): ?int
    {
        return $this->hauteur;
    }

    public function setHauteur(int $hauteur): self
    {
        $this->hauteur = $hauteur;

        return $this;
    }

    public function getLargeur(): ?int
    {
        return $this->largeur;
    }

    public function setLargeur(int $largeur): self
    {
        $this->largeur = $largeur;

        return $this;
    }

    public function getPoids(): ?int
    {
        return $this->poids;
    }

    public function setPoids(int $poids): self
    {
        $this->poids = $poids;

        return $this;
    }

    public function getPrix(): ?int
    {
        return $this->prix;
    }

    public function setPrix(int $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function isFragile(): ?bool
    {
        return $this->fragile;
    }

    public function setFragile(bool $fragile): self
    {
        $this->fragile = $fragile;

        return $this;
    }

    public function isInflammable(): ?bool
    {
        return $this->inflammable;
    }

    public function setInflammable(bool $inflammable): self
    {
        $this->inflammable = $inflammable;

        return $this;
    }

    public function getDepart(): ?string
    {
        return $this->depart;
    }

    public function setDepart(string $depart): self
    {
        $this->depart = $depart;

        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setDestination(string $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    public function getEtatColis(): ?string
    {
        return $this->etat_colis;
    }

    public function setEtatColis(?string $etat_colis): self
    {
        $this->etat_colis = $etat_colis;

        return $this;
    }

    public function getZone(): ?string
    {
        return $this->zone;
    }

    public function setZone(string $zone): self
    {
        $this->zone = $zone;

        return $this;
    }

    public function isUrgent(): ?bool
    {
        return $this->urgent;
    }

    public function setUrgent(bool $urgent): self
    {
        $this->urgent = $urgent;

        return $this;
    }

    public function getIdPartenaire(): ?Partenaire
    {
        return $this->id_partenaire;
    }

    public function setIdPartenaire(?Partenaire $id_partenaire): self
    {
        $this->id_partenaire = $id_partenaire;

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

    public function getIdClient(): ?Client
    {
        return $this->id_client;
    }

    public function setIdClient(?Client $client): self
    {
        $this->id_client = $client;
    
        return $this;
    }
    
}