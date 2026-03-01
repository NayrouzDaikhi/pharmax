<?php

namespace App\Entity;



use App\Repository\ArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Commentaire;



#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $contenu = null;

    #[ORM\Column(type: 'text', nullable: true, name: 'contenu_en')]
    private ?string $contenuEn = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $image = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, name: 'created_at')]
    private ?\DateTimeInterface $date_creation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, name: 'updated_at')]
    private ?\DateTimeInterface $date_modification = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $likes = 0;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isDraft = true;

    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Commentaire::class, orphanRemoval: true)]
    private Collection $commentaires;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;
        return $this;
    }

    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): static
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function getContenuEn(): ?string
    {
        return $this->contenuEn;
    }

    public function setContenuEn(?string $contenuEn): static
    {
        $this->contenuEn = $contenuEn;
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


    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): static
    {
        $this->date_creation = $date_creation;
        return $this;
    }

    public function getDateModification(): ?\DateTimeInterface
    {
        return $this->date_modification;
    }

    public function setDateModification(?\DateTimeInterface $date_modification): static
    {
        $this->date_modification = $date_modification;
        return $this;
    }

    public function getLikes(): int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): static
    {
        $this->likes = $likes;
        return $this;
    }

    public function incrementLikes(): static
    {
        $this->likes++;
        return $this;
    }

    public function decrementLikes(): static
    {
        if ($this->likes > 0) {
            $this->likes--;
        }
        return $this;
    }

    public function __construct()
    {
        $this->commentaires = new ArrayCollection();
        $this->date_creation = new \DateTime('now');
        $this->date_modification = new \DateTime('now');
    }


    public function getCommentaires(): Collection
    {
    return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): static
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires->add($commentaire);
            $commentaire->setArticle($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): static
    {
        if ($this->commentaires->removeElement($commentaire)) {
            if ($commentaire->getArticle() === $this) {
                $commentaire->setArticle(null);
            }
        }

        return $this;
    }

    public function isDraft(): bool
    {
        return $this->isDraft;
    }

    public function setIsDraft(bool $isDraft): static
    {
        $this->isDraft = $isDraft;
        return $this;
    }

    public function publish(): static
    {
        $this->isDraft = false;
        return $this;
    }

    public function saveDraft(): static
    {
        $this->isDraft = true;
        return $this;
    }}