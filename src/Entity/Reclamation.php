<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 8)]
    #[Assert\NotBlank(message: "L'ID de la commande est obligatoire.")]
    #[Assert\Regex(pattern: "/^\d+$/", message: "L'ID commande doit contenir uniquement des nombres.")]
    #[Assert\Length(max: 8, maxMessage: "L'ID commande ne doit pas dépasser 8 chiffres.")]
    private ?string $id_commande = null;

    #[ORM\Column(length: 8)]
    #[Assert\NotBlank(message: "L'ID du produit est obligatoire.")]
    #[Assert\Regex(pattern: "/^\d+$/", message: "L'ID produit doit contenir uniquement des nombres.")]
    #[Assert\Length(max: 8, maxMessage: "L'ID produit ne doit pas dépasser 8 chiffres.")]
    private ?string $id_produit = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La raison est obligatoire.")]
    #[Assert\Regex(pattern: "/^[a-zA-Z ]*$/", message: "La raison doit contenir uniquement des lettres et des espaces.")]
    private ?string $raison = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date est obligatoire.")]
    private ?\DateTimeInterface $date = null;

    /**
     * @var Collection<int, Remboursement>
     */
    #[ORM\OneToMany(targetEntity: Remboursement::class, mappedBy: 'id_reclamation')]
    private Collection $remboursements;

    public function __construct()
    {
        $this->remboursements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdCommande(): ?string
    {
        return $this->id_commande;
    }

    public function setIdCommande(string $id_commande): static
    {
        $this->id_commande = $id_commande;

        return $this;
    }

    public function getIdProduit(): ?string
    {
        return $this->id_produit;
    }

    public function setIdProduit(string $id_produit): static
    {
        $this->id_produit = $id_produit;

        return $this;
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
            // set the owning side to null (unless already changed)
            if ($remboursement->getIdReclamation() === $this) {
                $remboursement->setIdReclamation(null);
            }
        }

        return $this;
    }
}
