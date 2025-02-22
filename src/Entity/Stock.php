<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StockRepository::class)]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La quantité ne doit pas être vide.")]
    #[Assert\Positive(message: "La quantité de stocks doît être un nombre positif.")]
    private ?int $quantite = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La quantité minimale ne doit pas être vide.")]
    #[Assert\Positive(message: "La quantité de stocks minimale doit être un nombre positif.")]
    private ?int $quantite_min = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La quantité maximale ne doit pas être vide.")]
    #[Assert\Positive(message: "La quantité de stocks maximale doit être un nombre positif.")]
    private ?int $quantite_max = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date_creation = null;

    #[ORM\Column(length: 15)]
    #[Assert\Choice(choices: ['disponible', 'indisponible'], message: "Le statut doit être 'disponible' ou 'indisponible'.")]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $last_update_date = null;

    #[ORM\ManyToOne(inversedBy: 'stock_storage')]
    private ?Depot $depot = null;

    public function __construct()
    {
        $this->date_creation = new \DateTimeImmutable(); // Auto-set creation date
        $this->last_update_date = new \DateTime();//ensures that it is not null 
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->last_update_date = new \DateTime(); // Auto-update modification date
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getQuantiteMin(): ?int
    {
        return $this->quantite_min;
    }

    public function setQuantiteMin(int $quantite_min): static
    {
        $this->quantite_min = $quantite_min;

        return $this;
    }

    public function getQuantiteMax(): ?int
    {
        return $this->quantite_max;
    }

    public function setQuantiteMax(int $quantite_max): static
    {
        $this->quantite_max = $quantite_max;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeImmutable $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getLastUpdateDate(): ?\DateTimeInterface
    {
        return $this->last_update_date;
    }

    public function setLastUpdateDate(\DateTimeInterface $last_update_date): static
    {
        $this->last_update_date = $last_update_date;

        return $this;
    }

    public function getDepot(): ?Depot
    {
        return $this->depot;
    }

    public function setDepot(?Depot $depot): static
    {
        $this->depot = $depot;

        return $this;
    }
}
