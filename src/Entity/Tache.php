<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Utilisateur;
use App\Enum\StatutTache;

#[ORM\Entity(repositoryClass: "App\Repository\TacheRepository")]
#[ORM\Table(name:"taches")]
class Tache
{
    #[ORM\Id, ORM\GeneratedValue]
    #[ORM\Column(type:"integer")]
    private ?int $id = null;

    #[ORM\Column(type:"string", length:255)]
    private string $titre;

    #[ORM\Column(type:"text")]
    private string $description;

    #[ORM\Column(type:"string", length:20)]
    private string $statut;

    #[ORM\Column(type:"json", nullable:true)]
    private array $fichiers = []; // multi-upload

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable:false)]
    private Utilisateur $utilisateur;

    public function __construct()
    {
        $this->statut = StatutTache::A_FAIRE->value; // statut par défaut
    }

    // --- Getters / Setters ---

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getStatut(): StatutTache
    {
        return StatutTache::from($this->statut);
    }

    public function setStatut(StatutTache $statut): self
    {
        $this->statut = $statut->value;
        return $this;
    }

    public function getFichiers(): array
    {
        return $this->fichiers;
    }

    public function setFichiers(array $fichiers): self
    {
        $this->fichiers = $fichiers;
        return $this;
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }
}
