<?php

namespace App\Entity;

use App\Repository\ProductsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Utilisateur;

#[ORM\Entity(repositoryClass: ProductsRepository::class)]
class Products
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $price = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $categorie = null;

    #[ORM\Column(length: 255)]
    private ?string $location = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\Column(length: 255)]
    private ?string $quantity = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: "products")]
    #[ORM\JoinColumn(nullable: false)]  
      private ?Utilisateur $utilisateur = null;

    /**
     * @var Collection<int, Stock>
     */
    #[ORM\OneToMany(targetEntity: Stock::class, mappedBy: 'product')]
    private Collection $associated_stocks;


      /**
     * @var Collection<int, Reclamation>
     */
    #[ORM\OneToMany(targetEntity: Reclamation::class, mappedBy: 'id_produit')]
    private Collection $reclamations;

    public function __construct()
    {
        $this->associated_stocks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;
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

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(string $categorie): static
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;
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

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(string $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): static
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * @return Collection<int, Stock>
     */
    public function getAssociatedStocks(): Collection
    {
        return $this->associated_stocks;
    }

    public function addAssociatedStock(Stock $associatedStock): static
    {
        if (!$this->associated_stocks->contains($associatedStock)) {
            $this->associated_stocks->add($associatedStock);
            $associatedStock->setProduct($this);
        }

        return $this;
    }

    public function removeAssociatedStock(Stock $associatedStock): static
    {
        if ($this->associated_stocks->removeElement($associatedStock)) {
            // set the owning side to null (unless already changed)
            if ($associatedStock->getProduct() === $this) {
                $associatedStock->setProduct(null);
            }
        }

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
        // Set the product (Produit) for the reclamation (this should be correct)
        $reclamation->setIdProduit($this);
    }

    return $this;
}

    public function removeReclamation(Reclamation $reclamation): static
    {
        if ($this->reclamations->removeElement($reclamation)) {
            // set the owning side to null (unless already changed)
            if ($reclamation->getIdProduit() === $this) {
                $reclamation->setIdProduit(null);
            }
        }

        return $this;
    }

    public function __toString(): string
{
    return (string) $this->getId();  // Ou une autre propriété que tu veux afficher
}
}
