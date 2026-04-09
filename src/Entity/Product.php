<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use App\Entity\ProductTypeVariation;
use App\Entity\Size;
use App\Entity\Color;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Product
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(length: 125, unique: true, nullable: false)]
    private string $name = '';

    #[ORM\Column(length: 75, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $shortDescription = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $description = '';

    #[ORM\Column(options: ['default' => 0])]
    private int $salesCount = 0;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $price = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $compareAtPrice = null;

    #[ORM\Column(nullable:false, options: ['default' => 0])]
    private int $stock = 0;

    #[ORM\Column(
        name: 'rating_average',
        type: Types::DECIMAL,
        precision: 3,
        scale: 1,
        nullable: true
    )]
    private ?string $rating_average = null;

    #[ORM\Column(nullable: true, options: ['default' => 0])]
    private int $ratingCount = 0;

    #[ORM\Column(options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Brand $brand = null;

    /**
     * @var Collection<int, ProductImage>
     */
    #[ORM\OneToMany(
        targetEntity: ProductImage::class,
        mappedBy: 'product',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $productImages;

    /**
     * @var Collection<int, Review>
     */
    #[ORM\OneToMany(
        mappedBy: 'product',
        targetEntity: Review::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $reviews;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'products')]
    private Collection $categories;

    /**
     * @var Collection<int, CartItem>
     */
    #[ORM\OneToMany(
        targetEntity: CartItem::class,
        mappedBy: 'product',
        orphanRemoval: true
    )]
    private Collection $cartItems;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'product')]
    private Collection $orderItems;

    #[ORM\ManyToOne]
    private ?ProductTypeVariation $typeVariation = null;

    /**
     * @var Collection<int, Size>
     */
    #[ORM\ManyToMany(targetEntity: Size::class)]
    private Collection $sizes;

    /**
     * @var Collection<int, Color>
     */
    #[ORM\ManyToMany(targetEntity: Color::class)]
    private Collection $colors;

    /**
     * @var Collection<int, ProductColorVariant>
     */
    #[ORM\OneToMany(targetEntity: ProductColorVariant::class, mappedBy: 'product', cascade:['persist', 'remove'], orphanRemoval: true)]
    private Collection $colorVariants;

    /**
     * @var Collection<int, Coupon>
     */
    #[ORM\ManyToMany(targetEntity: Coupon::class, mappedBy: 'products')]
    private Collection $coupons;

    /**
     * @var Collection<int, Wishlist>
     */
    #[ORM\OneToMany(targetEntity: Wishlist::class, mappedBy: 'product')]
    private Collection $wishlists;

    public function __construct()
    {
        $this->productImages = new ArrayCollection();
        $this->reviews       = new ArrayCollection();
        $this->categories    = new ArrayCollection();
        $this->cartItems     = new ArrayCollection();
        $this->orderItems = new ArrayCollection();
        $this->sizes         = new ArrayCollection();
        $this->colors        = new ArrayCollection();
        $this->colorVariants = new ArrayCollection();
        $this->coupons = new ArrayCollection();
        $this->wishlists = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(?string $shortDescription): static
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

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

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getRatingAverage(): ?string
    {
        return $this->rating_average;
    }

    public function setRatingAverage(?string $ratingAverage): static
    {
        $this->rating_average = $ratingAverage;

        return $this;
    }

    public function getRatingCount(): int
    {
        return $this->ratingCount;
    }

    public function setRatingCount(int $ratingCount): static
    {
        $this->ratingCount = $ratingCount;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @return Collection<int, ProductImage>
     */
    public function getProductImages(): Collection
    {
        return $this->productImages;
    }

    public function addProductImage(ProductImage $productImage): static
    {
        if (!$this->productImages->contains($productImage)) {
            $this->productImages->add($productImage);
            $productImage->setProduct($this);
        }

        return $this;
    }

    public function removeProductImage(ProductImage $productImage): static
    {
        if ($this->productImages->removeElement($productImage)) {
            if ($productImage->getProduct() === $this) {
                $productImage->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setProduct($this);
        }
        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            if ($review->getProduct() === $this) {
                $review->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getSalesCount(): int
    {
        return $this->salesCount;
    }

    public function setSalesCount(int $salesCount): static
    {
        $this->salesCount = $salesCount;

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
            $cartItem->setProduct($this);
        }

        return $this;
    }

    public function removeCartItem(CartItem $cartItem): static
    {
        if ($this->cartItems->removeElement($cartItem)) {
            if ($cartItem->getProduct() === $this) {
                $cartItem->setProduct(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getMainImage(): ?ProductImage
    {
        // 1) Priorité : image principale SANS couleur
        foreach ($this->productImages as $image) {
            if ($image->isMain() && $image->getColor() === null) {
                return $image;
            }
        }

        // 2) À défaut : première image SANS couleur
        foreach ($this->productImages as $image) {
            if ($image->getColor() === null) {
                return $image;
            }
        }

        // 3) À défaut : n'importe quelle image principale (avec couleur)
        foreach ($this->productImages as $image) {
            if ($image->isMain()) {
                return $image;
            }
        }

        // 4) Dernière chance : la première image de la collection
        return $this->productImages->first() ?: null;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setProductId($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getProductId() === $this) {
                $orderItem->setProductId(null);
            }
        }

        return $this;
    }

    public function getTypeVariation(): ?ProductTypeVariation
    {
        return $this->typeVariation;
    }

    public function setTypeVariation(?ProductTypeVariation $typeVariation): static
    {
        $this->typeVariation = $typeVariation;

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
     * @return Collection<int, Color>
     */
    public function getColors(): Collection
    {
        return $this->colors;
    }

    public function addColor(Color $color): static
    {
        if (!$this->colors->contains($color)) {
            $this->colors->add($color);
        }

        return $this;
    }

    public function removeColor(Color $color): static
    {
        $this->colors->removeElement($color);

        return $this;
    }

    /**
     * @return Collection<int, ProductColorVariant>
     */
    public function getColorVariants(): Collection
    {
        return $this->colorVariants;
    }

    public function addColorVariant(ProductColorVariant $variant): static
    {
        if (!$this->colorVariants->contains($variant)) {
            $this->colorVariants->add($variant);
            $variant->setProduct($this);
        }

        return $this;
    }

    public function removeColorVariant(ProductColorVariant $variant): static
    {
        if ($this->colorVariants->removeElement($variant)) {
            if ($variant->getProduct() === $this) {
                $variant->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Coupon>
     */
    public function getCoupons(): Collection
    {
        return $this->coupons;
    }

    public function addCoupon(Coupon $coupon): static
    {
        if (!$this->coupons->contains($coupon)) {
            $this->coupons->add($coupon);
            $coupon->addProduct($this);
        }

        return $this;
    }

    public function removeCoupon(Coupon $coupon): static
    {
        if ($this->coupons->removeElement($coupon)) {
            $coupon->removeProduct($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Wishlist>
     */
    public function getWishlists(): Collection
    {
        return $this->wishlists;
    }

    public function addWishlist(Wishlist $wishlist): static
    {
        if (!$this->wishlists->contains($wishlist)) {
            $this->wishlists->add($wishlist);
            $wishlist->setProduct($this);
        }

        return $this;
    }

    public function removeWishlist(Wishlist $wishlist): static
    {
        if ($this->wishlists->removeElement($wishlist)) {
            // set the owning side to null (unless already changed)
            if ($wishlist->getProduct() === $this) {
                $wishlist->setProduct(null);
            }
        }

        return $this;
    }
}