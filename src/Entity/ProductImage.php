<?php

namespace App\Entity;

use App\Repository\ProductImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich; 

#[ORM\Entity(repositoryClass: ProductImageRepository::class)]
#[Vich\Uploadable()]
class ProductImage
{
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'productImages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Product $product = null;

    // NOM DE FICHIER GÉRÉ PAR VICH (COLONNE EN BDD)
    #[ORM\Column(length: 255,nullable:true)]
    private ?string $path = null;

    // FICHIER UPLOADÉ (NON PERSISTÉ EN BDD)
    #[Vich\UploadableField(mapping: 'products', fileNameProperty: 'path')]
    private ?File $imageFile = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createAt;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt;

    #[ORM\Column(nullable: false, options: ['default' => false], type: 'boolean')]
    private ?bool $isMain = false;

    #[ORM\ManyToOne(inversedBy: 'productImages')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Color $color = null;

    public function __construct()
    {
        $this->createAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeImmutable
    {
        return $this->createAt;
    }

    public function setCreateAt(\DateTimeImmutable $createAt): static
    {
        $this->createAt = $createAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
    public function setImageFile(?File $imageFile = null): static
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }
    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function isMain(): ?bool
    {
        return $this->isMain;
    }

    public function setIsMain(?bool $isMain): static
    {
        $this->isMain = $isMain;

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

}