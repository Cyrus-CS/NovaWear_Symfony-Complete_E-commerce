<?php
namespace App\Controller;

use App\Entity\ProductColorVariant;
use App\Entity\ProductImage;
use App\Form\ProductColorVariantType;
use App\Repository\ProductColorVariantRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\SizeRepository;

#[Route('admin/', name: 'admin_')]
class ProductColorImageController extends AbstractController
{
    #[Route('product/{slug}/images/colors', name: 'product_color_images', requirements: ['slug' => '[a-z0-9\-]+'])]
    public function manageColorImages(
        string $slug,
        Request $request,
        ProductRepository $productRepository,
        SizeRepository $sizeRepository,
        ProductColorVariantRepository $variantRepository,
        EntityManagerInterface $em,
        FormFactoryInterface $formFactory
    ): Response {
        $product = $productRepository->findOneBy(['slug' => $slug]);
        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        $colors = $product->getColors();
        $typeVariation = $product->getTypeVariation();
        if ($typeVariation) {
            // toutes les tailles possibles pour ce type de produit
            $availableSizes = $typeVariation->getSizes()->toArray();
        } else {
            // fallback : toutes les tailles de la base
            $availableSizes = $sizeRepository->findAll();
        }

        // On va garder les variants et formulaires indexés par color.id
        $variants = [];
        $forms    = [];

        foreach ($colors as $color) {
            // Chercher une variante existante pour (product, color)
            $variant = $variantRepository->findOneBy([
                'product' => $product,
                'color'   => $color,
            ]);

            if (!$variant) {
                // Pas encore de variante pour cette couleur → objet temporaire
                $variant = new ProductColorVariant();
            }

            $variants[$color->getId()] = $variant;

            // Formulaire nommé unique : color_variant_4, color_variant_7, ...
            $forms[$color->getId()] = $formFactory->createNamed(
        'color_variant_' . $color->getId(),
            ProductColorVariantType::class,
            $variant,
            [
                            'available_sizes' => $availableSizes,   
                    ]
                );
        }

        // Gestion du POST pour UNE seule couleur
        if ($request->isMethod('POST')) {
            $submittedColorId = $request->request->get('color_id');

            // Vérifier qu'on a bien cette couleur + formulaire associé
            $submittedColor = null;
            foreach ($colors as $color) {
                if ($color->getId() == $submittedColorId) {
                    $submittedColor = $color;
                    break;
                }
            }

            if ($submittedColor && isset($forms[$submittedColorId])) {
                $form    = $forms[$submittedColorId];
                $variant = $variants[$submittedColorId];

                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {

                    // Si c'est une nouvelle variante (jamais persistée)
                    if (null === $variant->getId()) {
                        $variant->setProduct($product);
                        $variant->setColor($submittedColor);
                        $em->persist($variant);
                    }

                    // ── Gestion des images, comme avant ──

                    /** @var \Symfony\Component\HttpFoundation\File\UploadedFile|null $mainFile */
                    $mainFile = $form->get('mainImage')->getData();

                    if ($mainFile) {
                        // Désactiver l'ancienne image principale de cette couleur, s'il y en a une
                        foreach ($product->getProductImages() as $existingImage) {
                            if (
                                $existingImage->getColor()
                                && $existingImage->getColor()->getId() === $submittedColor->getId()
                                && $existingImage->isMain()
                            ) {
                                $existingImage->setIsMain(false);
                            }
                        }

                        $productImage = new ProductImage();
                        $productImage->setImageFile($mainFile);
                        $productImage->setIsMain(true);
                        $productImage->setColor($submittedColor);
                        $productImage->setProduct($product);
                        $em->persist($productImage);
                    }

                    $secondaryFiles = $form->get('secondaryImages')->getData() ?? [];
                    foreach ($secondaryFiles as $file) {
                        $productImage = new ProductImage();
                        $productImage->setImageFile($file);
                        $productImage->setIsMain(false);
                        $productImage->setColor($submittedColor);
                        $productImage->setProduct($product);
                        $em->persist($productImage);
                    }

                    $em->flush();

                    $this->addFlash('success', 'Variante mise à jour pour la couleur ' . $submittedColor->getName());
                    return $this->redirectToRoute('admin_product_color_images', [
                        'slug' => $product->getSlug()
                    ]);
                } else {
                    // Debug utile en cas de souci
                    dump([
                        'colorId'    => $submittedColorId,
                        'isSubmitted'=> $form->isSubmitted(),
                        'isValid'    => $form->isValid(),
                        'errors'     => (string) $form->getErrors(true, true),
                    ]);
                }
            }
        }

        // Vues de formulaire
        $formViews = [];
        foreach ($colors as $color) {
            $formViews[$color->getId()] = $forms[$color->getId()]->createView();
        }

        // Images existantes par couleur (inchangé)
        $existingImages = [];
        foreach ($colors as $color) {
            $existingImages[$color->getId()] = [];
            foreach ($product->getProductImages() as $image) {
                if ($image->getColor() && $image->getColor()->getId() === $color->getId()) {
                    $existingImages[$color->getId()][] = $image;
                }
            }
        }

        return $this->render('admin/product/color_images.html.twig', [
            'product'        => $product,
            'colors'         => $colors,
            'formViews'      => $formViews,
            'existingImages' => $existingImages,
        ]);
    }

    #[Route('product-image/{id}/delete', name: 'product_image_delete', methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        $image = $em->getRepository(ProductImage::class)->find($id);

        if (!$image) {
            throw $this->createNotFoundException('Image introuvable.');
        }

        if (!$this->isCsrfTokenValid('delete_image_' . $id, $request->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('admin_product_color_images', [
                'slug' => $image->getProduct()->getSlug()
            ]);
        }

        $slug = $image->getProduct()->getSlug();
        $em->remove($image);
        $em->flush();

        $this->addFlash('success', 'Image supprimée.');
        return $this->redirectToRoute('admin_product_color_images', ['slug' => $slug]);
    }
}