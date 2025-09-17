<?php

namespace App\Entity;

use App\Repository\BlackFridayRepository;
use App\Entity\Product;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlackFridayRepository::class)]
class BlackFriday
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $produit = null;

    #[ORM\Column(type: 'float')]
    private ?float $nouveauPrix = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateCreation = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduit(): ?Product
    {
        return $this->produit;
    }

    public function setProduit(?Product $produit): self
    {
        $this->produit = $produit;
        return $this;
    }

    public function getNouveauPrix(): ?float
    {
        return $this->nouveauPrix;
    }

    public function setNouveauPrix(float $nouveauPrix): self
    {
        $this->nouveauPrix = $nouveauPrix;
        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;
        return $this;
    }
}
