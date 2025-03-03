<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use App\Service\SmsService;
#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
#[Vich\Uploadable] 
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Reason is required.")]
    #[Assert\Regex(pattern: "/^[a-zA-Z ]*$/", message: "Reason must contain only letters and spaces.")]
    private ?string $raison = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "Date is required.")]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $photo = null;  // ✅ Field storing the file name in DB

    #[Vich\UploadableField(mapping: "reclamation_images", fileNameProperty: "photo")]
    private ?File $photoFile = null; // ✅ Single field for the file

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    // New fields for Order ID and Product ID with validation rules
    #[ORM\ManyToOne(targetEntity: Checkout::class, inversedBy: 'reclamations')]
    #[Assert\NotBlank(message: "Order ID is required.")]
    #[Assert\Regex(pattern: "/^\d+$/", message: "Order ID must contain only numbers.")]
    #[Assert\Length(max: 8, maxMessage: "Order ID must not exceed 8 digits.")]
    private ?Checkout $id_commande = null;

    #[ORM\ManyToOne(targetEntity: Products::class, inversedBy: 'reclamations')]
    #[Assert\NotBlank(message: "Product ID is required.")]
    #[Assert\Regex(pattern: "/^\d+$/", message: "Product ID must contain only numbers.")]
    #[Assert\Length(max: 8, maxMessage: "Product ID must not exceed 8 digits.")]
    private ?Products $id_produit = null;

    private $smsService;

    

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): void
    {
        $this->photo = $photo;
    }

    public function setPhotoFile(?File $photoFile = null): void
    {
        $this->photoFile = $photoFile;
        
        // ✅ Auto-update for VichUploaderBundle
        if ($photoFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getPhotoFile(): ?File
    {
        return $this->photoFile;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @var Collection<int, Remboursement>
     */
    #[ORM\OneToMany(targetEntity: Remboursement::class, mappedBy: 'id_reclamation')]
    private Collection $remboursements;

   #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "reclamations")]
    #[ORM\JoinColumn(nullable: false)]  
    private ?Utilisateur $id_user ;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Regex(
        pattern: '/^\+?\d{8,15}$/',
        message: 'Invalid phone number format.'
    )]
    private ?string $phoneNumber = null;

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function __construct()
    {
        $this->remboursements = new ArrayCollection();
       
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRaison(): ?string
    {
        return $this->raison;
    }

    public function setRaison(string $raison): static
    {
        $this->raison = $raison;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return Collection<int, Remboursement>
     */
    public function getRemboursements(): Collection
    {
        return $this->remboursements;
    }

    public function addRemboursement(Remboursement $remboursement): static
    {
        if (!$this->remboursements->contains($remboursement)) {
            $this->remboursements->add($remboursement);
            $remboursement->setIdReclamation($this);
        }
        return $this;
    }

    public function removeRemboursement(Remboursement $remboursement): static
    {
        if ($this->remboursements->removeElement($remboursement)) {
            if ($remboursement->getIdReclamation() === $this) {
                $remboursement->setIdReclamation(null);
            }
        }
        return $this;
    }

    public function getIdUser(): ?Utilisateur
    {
        return $this->id_user;
    }

    public function setIdUser(?Utilisateur $id_user): static
    {
        $this->id_user = $id_user;

        return $this;
    }

    public function getIdProduit(): ?Products
    {
        return $this->id_produit;
    }

    public function setIdProduit(?Products $id_produit): static
    {
        $this->id_produit = $id_produit;

        return $this;
    }

    public function getIdCommande(): ?Checkout
    {
        return $this->id_commande;
    }

    public function setIdCommande(?Checkout $id_commande): static
    {
        $this->id_commande = $id_commande;

        return $this;
    }
}
