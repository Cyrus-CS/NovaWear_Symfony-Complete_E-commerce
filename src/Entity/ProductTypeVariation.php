<?php

namespace App\Entity;

use App\Repository\ProductTypeVariationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductTypeVariationRepository::class)]
class ProductTypeVariation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Size>
     */
    #[ORM\OneToMany(targetEntity: Size::class, mappedBy: 'productTypeVariation')]
    private Collection $sizes;

    public function __construct()
    {
        $this->sizes = new ArrayCollection();
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
            $size->setProductTypeVariation($this);
        }

        return $this;
    }

    public function removeSize(Size $size): static
    {
        if ($this->sizes->removeElement($size)) {
            // set the owning side to null (unless already changed)
            if ($size->getProductTypeVariation() === $this) {
                $size->setProductTypeVariation(null);
            }
        }

        return $this;
    }
}
