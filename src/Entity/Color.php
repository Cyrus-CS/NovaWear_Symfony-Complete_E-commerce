<?php

namespace App\Entity;

use App\Repository\ColorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ColorRepository::class)]
class Color
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: false, unique: true)]
    private ?string $name = null;

    #[ORM\Column(length: 15, unique:true, nullable: false)]
    private ?string $hex_code = null;

    #[ORM\OneToMany(mappedBy: 'color', targetEntity: ProductImage::class)]
    private Collection $productImages;

    /**
     * @var Collection<int, ProductColorVariant>
     */
    #[ORM\OneToMany(targetEntity: ProductColorVariant::class, mappedBy: 'color')]
    private Collection $productColorVariants;

    public function __construct()
    {
        $this->productImages = new ArrayCollection();
        $this->productColorVariants = new ArrayCollection();
    }

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

    public function getHexCode(): ?string
    {
        return $this->hex_code;
    }

    public function setHexCode(string $hex_code): static
    {
        $this->hex_code = $hex_code;

        return $this;
    }

    // GETTER — retourne toutes les images liées à cette couleur
    public function getProductImages(): Collection
    {
        return $this->productImages;
    }

    // ADD — ajoute une image et met à jour le côté propriétaire (ProductImage)
    public function addProductImage(ProductImage $productImage): static
    {
        if (!$this->productImages->contains($productImage)) {
            $this->productImages->add($productImage);
            $productImage->setColor($this); // synchronise le côté propriétaire
        }
        return $this;
    }

    // REMOVE — retire une image et met à jour le côté propriétaire
    public function removeProductImage(ProductImage $productImage): static
    {
        if ($this->productImages->removeElement($productImage)) {
            if ($productImage->getColor() === $this) {
                $productImage->setColor(null); // détache la couleur côté ProductImage
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, ProductColorVariant>
     */
    public function getProductColorVariants(): Collection
    {
        return $this->productColorVariants;
    }

    public function addProductColorVariant(ProductColorVariant $productColorVariant): static
    {
        if (!$this->productColorVariants->contains($productColorVariant)) {
            $this->productColorVariants->add($productColorVariant);
            $productColorVariant->setColor($this);
        }

        return $this;
    }

    public function removeProductColorVariant(ProductColorVariant $productColorVariant): static
    {
        if ($this->productColorVariants->removeElement($productColorVariant)) {
            // set the owning side to null (unless already changed)
            if ($productColorVariant->getColor() === $this) {
                $productColorVariant->setColor(null);
            }
        }

        return $this;
    }
    
}