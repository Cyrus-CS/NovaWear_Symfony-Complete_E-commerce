<?php
namespace App\Controller;

use App\Entity\ProductImage;
use App\Entity\Size;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SingleProductController extends AbstractController
{
    #[Route('/product/{slug}', name: 'product_single', methods: ['GET'], requirements: ['slug' => '[a-z0-9\-]+'])]
    public function show(string $slug, ProductRepository $productRepository): Response
    {
            $product = $productRepository->findOneBy(['slug' => $slug]);
            if (!$product) {
                throw $this->createNotFoundException('Produit introuvable.');
            }

            // 0) Tailles globales à afficher (toutes les tailles possibles pour ce produit)
            $allSizes = [];
            $typeVariation = $product->getTypeVariation();
            if ($typeVariation) {
                $allSizes = $typeVariation->getSizes()->toArray();
            } else {
                // fallback : union de toutes les tailles des variantes
                $seen = [];
                foreach ($product->getColorVariants() as $variant) {
                    foreach ($variant->getSizes() as $size) {
                        $id = $size->getId();
                        if (!isset($seen[$id])) {
                            $seen[$id] = $size;
                        }
                    }
                }
                $allSizes = array_values($seen);
            }

            // 1) colorGallery (inchangé, si tu t'en sers encore)
            $colorGallery = [];
            foreach ($product->getColors() as $color) {
                $colorImages  = [];
                $globalImages = [];

                foreach ($product->getProductImages() as $image) {
                    $data = [
                        'src'    => '/uploads/images/products/' . $image->getPath(),
                        'isMain' => $image->isMain(),
                    ];

                    if ($image->getColor() && $image->getColor()->getId() === $color->getId()) {
                        $colorImages[] = $data;
                    } elseif (!$image->getColor()) {
                        $globalImages[] = $data;
                    }
                }

                $colorGallery[$color->getId()] = [
                    'name'         => $color->getName(),
                    'hex'          => $color->getHexCode(),
                    'images'       => $colorImages,
                    'globalImages' => $globalImages,
                ];
            }

            // 2) galleryImages pour la lightbox (inchangé)
            $galleryImages = array_map(
                fn (ProductImage $img) => '/uploads/images/products/' . $img->getPath(),
                $product->getProductImages()->toArray()
            );

            // 3) colorVariants (prix / stock / tailles)
            $colorVariants = [];

            foreach ($product->getColorVariants() as $variant) {
                $color = $variant->getColor();
                if (!$color) {
                    continue;
                }
                $colorId = $color->getId();

                $sizesCollection = $variant->getSizes();
                $sizeIds = [];
                if (!$sizesCollection->isEmpty()) {
                    $sizeIds = array_map(
                        fn (Size $size) => $size->getId(),
                        $sizesCollection->toArray()
                    );
                } else {
                    // Variante existante sans taille cochée -> aucune taille autorisée
                    $sizeIds = [];
                }

                $colorVariants[$colorId] = [
                    'price'          => $variant->getPrice() !== null
                        ? (float) $variant->getPrice()
                        : (float) $product->getPrice(),

                    'compareAtPrice' => $variant->getCompareAtPrice() !== null
                        ? (float) $variant->getCompareAtPrice()
                        : ($product->getCompareAtPrice() !== null
                            ? (float) $product->getCompareAtPrice()
                            : null),

                    'stock'          => $variant->getStock() !== null
                        ? $variant->getStock()
                        : $product->getStock(),

                    'sizeIds'        => $sizeIds, // subset ou tableau vide = aucune taille
                ];
            }

            // Fallback : couleurs SANS variante → pas de restriction = toutes les tailles autorisées
            foreach ($product->getColors() as $color) {
                $cid = $color->getId();
                if (!isset($colorVariants[$cid])) {
                    $colorVariants[$cid] = [
                        'price'          => (float) $product->getPrice(),
                        'compareAtPrice' => $product->getCompareAtPrice() !== null
                            ? (float) $product->getCompareAtPrice()
                            : null,
                        'stock'          => $product->getStock(),
                        'sizeIds'        => null, // null => toutes tailles autorisées
                    ];
                }
            }

            return $this->render('product/product_show.html.twig', [
                'product'        => $product,
                'reviews'        => $product->getReviews(),
                'slug'           => $slug,
                'colorGallery'   => $colorGallery,
                'galleryImages'  => $galleryImages,
                'colorVariants'  => $colorVariants,
                'allSizes'       => $allSizes,
            ]);
        }

}