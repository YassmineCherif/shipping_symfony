<?php

namespace App\Entity;

use App\Repository\PartenaireRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: PartenaireRepository::class)]
class Partenaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $nom = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 8)]
    private ?string $numtel = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $Zone = null;
    
    #[ORM\Column(nullable: true,name:"Prix_Poids")]
    private ?float $PrixPoids = null;
    
    #[ORM\Column(length: 100, nullable: true,name:"Prix_Zone")]
    private ?string $PrixZone = null;
    
    #[ORM\Column(nullable: true)]
    private ?bool $inflammable = null;
    
    #[ORM\Column(nullable: true)]
    private ?bool $fragile = null;

    #[ORM\Column(length: 30,nullable: true)]
    private ?string $login = null;

    #[ORM\Column(length: 100)]
    private ?string $password = null;
    #[ORM\OneToMany(targetEntity: "App\Entity\Livreur", mappedBy: "partenaire")]
    private $livreur;

    
    #[ORM\ManyToOne(inversedBy: 'partenaires')]
    private ?User $user = null;

    public function __construct()
    {
        $this->livreur = new ArrayCollection();
    }

    /**
     * @return Collection|Livreur[]
     */
    public function getLivreurs(): Collection
    {
        return $this->livreur;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }
    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getNumtel(): ?string
    {
        return $this->numtel;
    }

    public function setNumtel(string $numtel): self
    {
        $this->numtel = $numtel;

        return $this;
    }

    public function getZone(): ?string
    {
        return $this->Zone;
    }

    public function setZone(string $Zone): self
    {
        $this->Zone = $Zone;

        return $this;
    }

    public function getPrixPoids(): ?float
    {
        return $this->PrixPoids;
    }

    public function setPrixPoids(float $PrixPoids): self
    {
        $this->PrixPoids = $PrixPoids;

        return $this;
    }

    public function getPrixZone(): ?string
    {
        return $this->PrixZone;
    }

    public function setPrixZone(string $PrixZone): self
    {
        $this->PrixZone = $PrixZone;

        return $this;
    }

    public function getInflammable(): ?bool
    {
        return $this->inflammable;
    }

    public function setInflammable(bool $inflammable): self
    {
        $this->inflammable = $inflammable;

        return $this;
    }

    public function getFragile(): ?bool
    {
        return $this->fragile;
    }

    public function setFragile(bool $fragile): self
    {
        $this->fragile = $fragile;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }


    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }



}