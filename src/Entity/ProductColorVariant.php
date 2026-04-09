<?php

namespace App\Entity;

use App\Repository\ProductColorVariantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductColorVariantRepository::class)]
class ProductColorVariant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    private ?string $price = null;

    #[ORM\Column(nullable: true)]
    private ?int $stock = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0, nullable: true)]
    private ?string $compareAtPrice = null;

    #[ORM\ManyToOne(targetEntity: Color::class, inversedBy: 'productColorVariants')]
     #[ORM\JoinColumn(nullable: false)]
    private ?Color $color = null;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'colorVariants')]
     #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    /**
     * @var Collection<int, Size>
     */
    #[ORM\ManyToMany(targetEntity: Size::class)]
    #[ORM\JoinTable(name: 'product_color_variant_size')]
    private Collection $sizes;

    /**
     * @var Collection<int, CartItem>
     */
    #[ORM\OneToMany(targetEntity: CartItem::class, mappedBy: 'variant')]
    private Collection $cartItems;

    public function __construct()
    {
        $this->sizes = new ArrayCollection();
        $this->cartItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getCompareAtPrice(): ?string
    {
        return $this->compareAtPrice;
    }

    public function setCompareAtPrice(?string $compareAtPrice): static
    {
        $this->compareAtPrice = $compareAtPrice;

        return $this;
    }

    public function getColor(): ?Color
    {
        return $this->color;
    }

    public function setColor(?Color $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return Collection<int, Size>
     */
    public function getSizes(): Collection
    {
        return $this->sizes;
    }

    public function addSize(Size $size): static
    {
        if (!$this->sizes->contains($size)) {
            $this->sizes->add($size);
        }

        return $this;
    }

    public function removeSize(Size $size): static
    {
        $this->sizes->removeElement($size);

        return $this;
    }

    /**
     * @return Collection<int, CartItem>
     */
    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }

    public function addCartItem(CartItem $cartItem): static
    {
        if (!$this->cartItems->contains($cartItem)) {
            $this->cartItems->add($cartItem);
            $cartItem->setVariant($this);
        }

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): static
    {
        if ($this->cartItems->removeElement($cartItem)) {
            // set the owning side to null (unless already changed)
            if ($cartItem->getVariant() === $this) {
                $cartItem->setVariant(null);
            }
        }

        return $this;
    }
}