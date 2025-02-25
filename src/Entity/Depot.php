<?php

namespace App\Entity;

use App\Repository\DepotRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use App\Entity\Stock;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;


#[ORM\Entity(repositoryClass: DepotRepository::class)]
#[Gedmo\SoftDeleteable(fieldName: "deletedAt", timeAware: false)]
class Depot
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide.")]
    #[Assert\Length(max: 20, maxMessage: "Le nom ne doit pas dépasser 20 caractères.")]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse est requise.")]
    private ?string $adresse = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La capacité de stockage est requise.")]
    #[Assert\Positive(message: "La capacité de stockage doit être un nombre positif.")]
    private ?int $stock_capacity = null;

    #[Gedmo\Timestampable(on: "create")]
    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeInterface $date_creation = null;

    
    #[ORM\Column(type: "datetime", nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    #[ORM\Column(length: 15)]
    #[Assert\NotBlank(message: "Le statut est requis.")]
    #[Assert\Choice(choices: ['Actif', 'Inactif','Maintenance'], message: "Le statut doit être 'actif' ou 'inactif' ou 'maintenance'.")]
    private ?string $status = null;

    #[Gedmo\Timestampable(on: "update")]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $last_update_date = null;

    /**
     * @var Collection<int, Stock>
     */
    #[ORM\OneToMany(targetEntity: Stock::class, mappedBy: 'depot')]
    private Collection $stock_storage;

    public function __construct()
    {
        $this->stock_storage = new ArrayCollection();
        $this->date_creation = new \DateTimeImmutable();
        $this->last_update_date = new \DateTime();//ensures that it is not null
        $this->deletedAt = null;
    }
    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->last_update_date = new \DateTime(); // Auto-update modification date
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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getStockCapacity(): ?int
    {
        return $this->stock_capacity;
    }

    public function setStockCapacity(int $stock_capacity): static
    {
        $this->stock_capacity = $stock_capacity;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeImmutable
{
    return $this->date_creation instanceof \DateTime
        ? \DateTimeImmutable::createFromMutable($this->date_creation)
        : $this->date_creation;
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

    /**
     * @return Collection<int, stock>
     */
    public function getStockStorage(): Collection
    {
        return $this->stock_storage;
    }

    public function addStockStorage(stock $stockStorage): static
    {
        if (!$this->stock_storage->contains($stockStorage)) {
            $this->stock_storage->add($stockStorage);
            $stockStorage->setDepot($this);
        }

        return $this;
    }

    public function removeStockStorage(stock $stockStorage): static
    {
        if ($this->stock_storage->removeElement($stockStorage)) {
            // set the owning side to null (unless already changed)
            if ($stockStorage->getDepot() === $this) {
                $stockStorage->setDepot(null);
            }
        }

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
    return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): static
    {
    $this->deletedAt = $deletedAt;
    return $this;
    }
}
