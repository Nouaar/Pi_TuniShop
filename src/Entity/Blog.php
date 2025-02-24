<?php

namespace App\Entity;
use App\Entity\Commentaire;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\BlogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlogRepository::class)]
class Blog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\Length(max:20,maxMessage:"The title must not surpass 20 characters ")]
    #[Assert\NotBlank(message:"The title can't be blank ")]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message:"The description can't be blank ")]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column]
    private ?int $nb_likes = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_creation = null;

    /**
     * @var Collection<int, Commentaire>
     */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'blog')]
    private Collection $comments_section;

    public function __construct()
    {
        $this->comments_section = new ArrayCollection();
        $this->date_creation = new \DateTime();
        $this->nb_likes = 0;
    }

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getNbLikes(): ?int
    {
        return $this->nb_likes;
    }

    public function setNbLikes(int $nb_likes): static
    {
        $this->nb_likes = $nb_likes;

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

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentsSection(): Collection
    {
        return $this->comments_section;
    }

    public function addCommentsSection(Commentaire $commentsSection): static
    {
        if (!$this->comments_section->contains($commentsSection)) {
            $this->comments_section->add($commentsSection);
            $commentsSection->setBlog($this);
        }

        return $this;
    }

    public function removeCommentsSection(Commentaire $commentsSection): static
    {
        if ($this->comments_section->removeElement($commentsSection)) {
            // set the owning side to null (unless already changed)
            if ($commentsSection->getBlog() === $this) {
                $commentsSection->setBlog(null);
            }
        }

        return $this;
    }

    public function addLike(): self { $this->nb_likes++; return $this; }

}
