<?php

namespace App\Entity;

use App\Repository\RemboursementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RemboursementRepository::class)]
class Remboursement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_remboursement = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le montant est obligatoire.")]
    #[Assert\Positive(message: "Le montant doit être un nombre positif.")]
    #[Assert\Regex(pattern: "/^\d+$/", message: "le montant  doit contenir uniquement des nombres.")]
    private ?string $montant = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le mode de remboursement est obligatoire.")]
    private ?string $mode_remboursement = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "La date est obligatoire.")]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(targetEntity: Reclamation::class, inversedBy: 'remboursements')]
    #[ORM\JoinColumn(name: "id", referencedColumnName: "id", nullable: false)]
    private ?Reclamation $id = null;

    public function getIdRemboursement(): ?int
    {
        return $this->id_remboursement;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(string $montant): static
    {
        $this->montant = $montant;
        return $this;
    }

    public function getModeRemboursement(): ?string
    {
        return $this->mode_remboursement;
    }

    public function setModeRemboursement(string $mode_remboursement): static
    {
        $this->mode_remboursement = $mode_remboursement;
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

    public function getIdReclamation(): ?Reclamation
    {
        return $this->id;
    }

    public function setIdReclamation(?Reclamation $id): static
    {
        $this->id = $id;
        return $this;
    }
}
