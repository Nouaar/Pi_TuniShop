<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements PasswordAuthenticatedUserInterface, UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 50)]
    private ?string $role = 'ROLE_USER'; // Default role

    #[ORM\Column(length: 255)]
    private ?string $password = null;
    
         /**
     * @var Collection<int, Reclamation>
     */
    #[ORM\ManyToMany(targetEntity: Reclamation::class, mappedBy: 'id_user')]
    private Collection $reclamations;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: AdresseUser::class, orphanRemoval: true, cascade: ['remove'])]
    private Collection $adresses;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $verificationToken = null;

    /**
     * @var Collection<int, Blog>
     */
    #[ORM\OneToMany(targetEntity: Blog::class, mappedBy: 'utilisateur')]
    private Collection $associated_blogs;

    /**
     * @var Collection<int, Commentaire>
     */
    #[ORM\OneToMany(targetEntity: Commentaire::class, mappedBy: 'utilisateur')]
    private Collection $associated_comments; // New field for email verification

    public function __construct()
    {
        $this->adresses = new ArrayCollection();
        $this->associated_blogs = new ArrayCollection();
        $this->associated_comments = new ArrayCollection();
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function getRoles(): array
    {
        return [$this->role]; // Symfony requires an array
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function getAdresses(): Collection
    {
        return $this->adresses;
    }

    public function addAdresse(AdresseUser $adresse): static
    {
        if (!$this->adresses->contains($adresse)) {
            $this->adresses[] = $adresse;
            $adresse->setUtilisateur($this);
        }
        return $this;
    }

    public function removeAdresse(AdresseUser $adresse): static
    {
        if ($this->adresses->removeElement($adresse)) {
            if ($adresse->getUtilisateur() === $this) {
                $adresse->setUtilisateur(null);
            }
        }
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
        // Clear temporary sensitive data if needed
    }

 

    public function getVerificationToken(): ?string
    {
        return $this->verificationToken;
    }

    public function setVerificationToken(?string $verificationToken): static
    {
        $this->verificationToken = $verificationToken;
        return $this;
    }




        /**
     * @return Collection<int, Reclamation>
     */
    public function getReclamations(): Collection
    {
        return $this->reclamations;
    }

    public function addReclamation(Reclamation $reclamation): static
    {
        if (!$this->reclamations->contains($reclamation)) {
            $this->reclamations->add($reclamation);
            $reclamation->setIdUser($this);
        }

        return $this;
    }

    public function removeReclamation(Reclamation $reclamation): static
    {
        if ($this->reclamations->removeElement($reclamation)) {
            // set the owning side to null (unless already changed)
            if ($reclamation->getIdUser() === $this) {
                $reclamation->setIdUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Blog>
     */
    public function getAssociatedBlogs(): Collection
    {
        return $this->associated_blogs;
    }

    public function addAssociatedBlog(Blog $associatedBlog): static
    {
        if (!$this->associated_blogs->contains($associatedBlog)) {
            $this->associated_blogs->add($associatedBlog);
            $associatedBlog->setUtilisateur($this);
        }

        return $this;
    }

    public function removeAssociatedBlog(Blog $associatedBlog): static
    {
        if ($this->associated_blogs->removeElement($associatedBlog)) {
            // set the owning side to null (unless already changed)
            if ($associatedBlog->getUtilisateur() === $this) {
                $associatedBlog->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getAssociatedComments(): Collection
    {
        return $this->associated_comments;
    }

    public function addAssociatedComment(Commentaire $associatedComment): static
    {
        if (!$this->associated_comments->contains($associatedComment)) {
            $this->associated_comments->add($associatedComment);
            $associatedComment->setUtilisateur($this);
        }

        return $this;
    }

    public function removeAssociatedComment(Commentaire $associatedComment): static
    {
        if ($this->associated_comments->removeElement($associatedComment)) {
            // set the owning side to null (unless already changed)
            if ($associatedComment->getUtilisateur() === $this) {
                $associatedComment->setUtilisateur(null);
            }
        }

        return $this;
    }
}
