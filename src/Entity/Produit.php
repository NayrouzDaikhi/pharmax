<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom du produit est requis')]
    #[Assert\Length(min: 3, max: 255, minMessage: 'Le nom doit contenir au moins 3 caractères', maxMessage: 'Le nom ne doit pas dépasser 255 caractères')]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La description est requise')]
    #[Assert\Length(min: 10, minMessage: 'La description doit contenir au moins 10 caractères')]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le prix est requis')]
    #[Assert\Positive(message: 'Le prix doit être positif')]
    private ?float $prix = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date d\'expiration est requise')]
    #[Assert\GreaterThan('today', message: 'La date d\'expiration doit être dans le futur')]
    private ?\DateTime $dateExpiration = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'Le statut est requis')]
    private ?bool $statut = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La quantité est requise')]
    #[Assert\GreaterThanOrEqual(0, message: 'La quantité doit être supérieure ou égale à 0')]
    private ?int $quantite = null;

    // Relation vers Categorie
    #[ORM\ManyToOne(targetEntity: Categorie::class, inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Categorie $categorie = null;

    // Relation vers Commentaire pour les avis/commentaires de produit
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'produit', cascade: ['remove'])]
    private Collection $avis;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->avis = new ArrayCollection();
    }

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getDateExpiration(): ?\DateTime
    {
        return $this->dateExpiration;
    }

    public function setDateExpiration(\DateTime $dateExpiration): static
    {
        $this->dateExpiration = $dateExpiration;
        return $this;
    }

    public function isStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(bool $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;
        return $this;
    }

    // English aliases for template compatibility
    public function getName(): ?string
    {
        return $this->nom;
    }

    public function setName(string $name): static
    {
        $this->nom = $name;
        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->prix;
    }

    public function setPrice(float $price): static
    {
        $this->prix = $price;
        return $this;
    }

    public function getStock(): ?int
    {
        return $this->quantite;
    }

    public function setStock(int $stock): static
    {
        $this->quantite = $stock;
        return $this;
    }

    public function getExpirationDate(): ?\DateTime
    {
        return $this->dateExpiration;
    }

    public function setExpirationDate(\DateTime $expirationDate): static
    {
        $this->dateExpiration = $expirationDate;
        return $this;
    }

    public function getReference(): ?string
    {
        return null;
    }

    public function getSku(): ?string
    {
        return null;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvis(Commentaire $avis): static
    {
        if (!$this->avis->contains($avis)) {
            $this->avis->add($avis);
            $avis->setProduit($this);
        }
        return $this;
    }

    public function removeAvis(Commentaire $avis): static
    {
        if ($this->avis->removeElement($avis)) {
            if ($avis->getProduit() === $this) {
                $avis->setProduit(null);
            }
        }
        return $this;
    }
}
