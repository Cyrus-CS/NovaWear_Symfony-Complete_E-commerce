<?php

namespace App\Entity;

use App\Repository\CouponRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CouponRepository::class)]
class Coupon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

     // Code utilisé par l'utilisateur (unique)
    #[ORM\Column(length: 50)]
    private ?string $code = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;
    
    // 'percent' ou 'fixed'
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $discountType = 'percent';

    // Valeur de la remise (ex: 20 pour -20%, ou 15.00 pour -15€)
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0, nullable: true)]
    private ?string $discountValue = null;

    // Date de début / fin de validité
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $startsAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isActive = true;

    // Montant minimum du panier pour que le coupon soit valide
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 0)]
    private ?string $minCartTotal = null;

    // Limite d'utilisation globale (facultatif)
    #[ORM\Column(nullable: true)]
    private ?int $maxUses = null;

    #[ORM\Column(nullable: true)]
    private ?int $usedCount = null;

    /**
     * @var Collection<int, Product>
     */
    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'coupons')]
    private Collection $products;

    /**
     * @var Collection<int, Cart>
     */
    #[ORM\OneToMany(targetEntity: Cart::class, mappedBy: 'coupon')]
    private Collection $carts;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->carts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDiscountType(): ?string
    {
        return $this->discountType;
    }

    public function setDiscountType(?string $discountType): static
    {
        $this->discountType = $discountType;

        return $this;
    }

    public function getStartsAt(): ?\DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(\DateTimeImmutable $startsAt): static
    {
        $this->startsAt = $startsAt;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(?bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getMinCartTotal(): ?string
    {
        return $this->minCartTotal;
    }

    public function setMinCartTotal(string $minCartTotal): static
    {
        $this->minCartTotal = $minCartTotal;

        return $this;
    }

    public function getMaxUses(): ?int
    {
        return $this->maxUses;
    }

    public function setMaxUses(?int $maxUses): static
    {
        $this->maxUses = $maxUses;

        return $this;
    }

    public function getUsedCount(): ?int
    {
        return $this->usedCount;
    }

    public function setUsedCount(?int $usedCount): static
    {
        $this->usedCount = $usedCount;

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

    public function getDiscountValue(): ?string
    {
        return $this->discountValue;
    }

    public function setDiscountValue(?string $discountValue): static
    {
        $this->discountValue = $discountValue;

        return $this;
    }

    /**
     * @return Collection<int, Cart>
     */
    public function getCarts(): Collection
    {
        return $this->carts;
    }

    public function addCart(Cart $cart): static
    {
        if (!$this->carts->contains($cart)) {
            $this->carts->add($cart);
            $cart->setCoupon($this);
        }

        return $this;
    }

    public function removeCart(Cart $cart): static
    {
        if ($this->carts->removeElement($cart)) {
            // set the owning side to null (unless already changed)
            if ($cart->getCoupon() === $this) {
                $cart->setCoupon(null);
            }
        }

        return $this;
    }
}