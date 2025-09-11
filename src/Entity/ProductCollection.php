<?php

namespace App\Entity;

use App\Repository\ProductCollectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductCollectionRepository::class)]
class ProductCollection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['collection:read', 'product:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Le nom doit contenir au moins {{ limit }} caractères',
        maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
    )]
    #[Groups(['collection:read', 'product:read'])]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['collection:read'])]
    private ?string $description1 = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['collection:read'])]
    private ?string $description2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "L'URL n'est pas valide")]
    #[Groups(['collection:read'])]
    private ?string $url1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "L'URL n'est pas valide")]
    #[Groups(['collection:read'])]
    private ?string $url2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "L'URL n'est pas valide")]
    #[Groups(['collection:read'])]
    private ?string $url3 = null;

    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'productCollections')]
    #[Groups(['collection:read'])]
    private Collection $products;

    #[ORM\Column]
    #[Groups(['collection:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    // ---- GETTERS & SETTERS ---- //

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription1(): ?string
    {
        return $this->description1;
    }

    public function setDescription1(?string $description1): static
    {
        $this->description1 = $description1;
        return $this;
    }

    public function getDescription2(): ?string
    {
        return $this->description2;
    }

    public function setDescription2(?string $description2): static
    {
        $this->description2 = $description2;
        return $this;
    }

    public function getUrl1(): ?string
    {
        return $this->url1;
    }

    public function setUrl1(?string $url1): static
    {
        $this->url1 = $url1;
        return $this;
    }

    public function getUrl2(): ?string
    {
        return $this->url2;
    }

    public function setUrl2(?string $url2): static
    {
        $this->url2 = $url2;
        return $this;
    }

    public function getUrl3(): ?string
    {
        return $this->url3;
    }

    public function setUrl3(?string $url3): static
    {
        $this->url3 = $url3;
        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        $this->products->removeElement($product);
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
