<?php

namespace App\Entity;

use App\Repository\LivreurRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LivreurRepository::class)]
#[ORM\Table(name: "livreur")]
class Livreur 
{
    #[ORM\ManyToOne(targetEntity: "App\Entity\Partenaire", inversedBy: "livreur")]
    #[ORM\JoinColumn(name: "id_partenaire", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private $id_partenaire;
    #[ORM\ManyToOne(inversedBy: 'livreurs')]
    private ?User $user = null;
       
 
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $nom = null;

    #[ORM\Column(length: 30)]
    private ?string $prenom = null;

    #[ORM\Column(length: 30)]
    private ?string $email = null;

    #[ORM\Column]
    private ?int $numtel = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbre_reclamation = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbre_colis_total = null;

    #[ORM\Column(nullable: true)]
    private ?int $nbre_colis_courant = null;

    #[ORM\Column(length: 100)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

   

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

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

    public function getNumtel(): ?int
    {
        return $this->numtel;
    }

    public function setNumtel(int $numtel): self
    {
        $this->numtel = $numtel;

        return $this;
    }

    public function getNbreReclamation(): ?int
    {
        return $this->nbre_reclamation;
    }

    public function setNbreReclamation(?int $nbre_reclamation): self
    {
        $this->nbre_reclamation = $nbre_reclamation;

        return $this;
    }

    public function getNbreColisTotal(): ?int
    {
        return $this->nbre_colis_total;
    }

    public function setNbreColisTotal(?int $nbre_colis_total): self
    {
        $this->nbre_colis_total = $nbre_colis_total;

        return $this;
    }

    public function getNbreColisCourant(): ?int
    {
        return $this->nbre_colis_courant;
    }

    public function setNbreColisCourant(?int $nbre_colis_courant): self
    {
        $this->nbre_colis_courant = $nbre_colis_courant;

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

   
public function getAdresse(): ?string
{
    return $this->adresse;
}

public function setAdresse(string $adresse): self
{
    $this->adresse = $adresse;

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
