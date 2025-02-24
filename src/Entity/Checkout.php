<?php

namespace App\Entity;

use App\Repository\CheckoutRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CheckoutRepository::class)]
class Checkout
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: "bigint")] // Store unique checkout session ID
    private ?int $checkoutId = null;

    #[ORM\Column(length: 255)]
    private ?string $first_name = null;

    #[ORM\Column(length: 255)]
    private ?string $second_name = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $street = null;

    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\Column(length: 255)]
    private ?string $postal_code = null;

    #[ORM\Column(length: 255)]
    private ?string $country = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: "id_user", referencedColumnName: "id", nullable: false)]
    private ?Utilisateur $id_user = null;

    #[ORM\ManyToOne(targetEntity: Products::class)]
    #[ORM\JoinColumn(name: "id_produit", referencedColumnName: "id")]
    private ?Products $id_produit = null;

    public function getId(): ?int { return $this->id; }

    public function getCheckoutId(): ?int { return $this->checkoutId; }

    public function setCheckoutId(int $checkoutId): self
    {
        $this->checkoutId = $checkoutId;
        return $this;
    }

    public function getFirstName(): ?string { return $this->first_name; }

    public function setFirstName(string $first_name): self
    {
        $this->first_name = $first_name;
        return $this;
    }

    public function getSecondName(): ?string { return $this->second_name; }

    public function setSecondName(string $second_name): self
    {
        $this->second_name = $second_name;
        return $this;
    }

    public function getEmail(): ?string { return $this->email; }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getStreet(): ?string { return $this->street; }

    public function setStreet(string $street): self
    {
        $this->street = $street;
        return $this;
    }

    public function getCity(): ?string { return $this->city; }

    public function setCity(string $city): self
    {
        $this->city = $city;
        return $this;
    }

    public function getPostalCode(): ?string { return $this->postal_code; }

    public function setPostalCode(string $postal_code): self
    {
        $this->postal_code = $postal_code;
        return $this;
    }

    public function getCountry(): ?string { return $this->country; }

    public function setCountry(string $country): self
    {
        $this->country = $country;
        return $this;
    }

    public function getIdUser(): ?Utilisateur { return $this->id_user; }

    public function setIdUser(Utilisateur $id_user): self
    {
        $this->id_user = $id_user;
        return $this;
    }

    public function getIdProduit(): ?Products { return $this->id_produit; }

    public function setIdProduit(?Products $id_produit): self
    {
        $this->id_produit = $id_produit;
        return $this;
    }
}