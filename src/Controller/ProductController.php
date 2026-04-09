<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/admin/product')]
final class ProductController extends AbstractController
{

    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(Request $request, ProductRepository $productRepository, PaginatorInterface $paginator): Response
    {
        $page = $request->query->getInt('page', 1);
        $pagination = $productRepository->paginateProduct($paginator, $page);

        return $this->render('admin/product/index.html.twig', [
            'products' => $pagination,
        ]);
    }
 
    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile|null $mainFile */
            $mainFile = $form->get('mainImage')->getData();

            /** @var UploadedFile[] $imageFiles */
            $imageFiles = $form->get('images')->getData();

            // bloquer si aucune image
            if (empty($imageFiles) && !$mainFile) {
                $this->addFlash('danger', 'Vous devez ajouter au moins une image.');
                return $this->render('admin/product/new.html.twig', [
                    'product' => $product,
                    'form' => $form,
                ]);
            }

            // 1- Image principale (si fournie)
            if ($mainFile) {
                $mainImage = new ProductImage();
                $mainImage->setImageFile($mainFile);
                $mainImage->setIsMain(true);
                $product->addProductImage($mainImage);
            }

            // 2- Images secondaires (toujours isMain = false ici)
            foreach ($imageFiles as $imageFile) {
                $productImage = new ProductImage();
                $productImage->setImageFile($imageFile);
                $productImage->setIsMain(false);
                $product->addProductImage($productImage);
            }

            // 3- Si pas d’image principale mais des images secondaires → première en main
            if (!$mainFile && $product->getProductImages()->count() > 0) {
                $product->getProductImages()->first()->setIsMain(true);
            }

            $entityManager->persist($product);
            $entityManager->flush();

            // Redirige vers la page images couleurs
            $this->addFlash('success', 'Produit créé ! Si vous voulez, ajoutez maintenant les images par couleur.');
            return $this->redirectToRoute('admin_product_color_images', [
                'slug' => $product->getSlug()
            ]);
        }

        return $this->render('admin/product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(string $id, ProductRepository $productRepository): Response
    {
        $product = $productRepository->find(Uuid::fromString($id));
        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        return $this->render('admin/product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, string $id, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        $product = $productRepository->find(Uuid::fromString($id));
        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile|null $mainFile */
            $mainFile = $form->get('mainImage')->getData();

            //getData() : Renvoie les données du modèle dans le format requis par l'objet sous-jacent.retourne un tableau de fichiers uploadés.
            
            /** @var UploadedFile[] $imageFiles */
            $imageFiles = $form->get('images')->getData();

            // 1) Nouvelle image principale globale ?
            if ($mainFile) {
                // dé-marquer l'ancienne image principale globale
                foreach ($product->getProductImages() as $img) {
                    if ($img->getColor() === null && $img->isMain()) {
                        $img->setIsMain(false);
                    }
                }

                $mainImage = new ProductImage();
                $mainImage->setImageFile($mainFile);
                $mainImage->setIsMain(true);
                $product->addProductImage($mainImage);
            }

            // 2) Nouvelles images secondaires
            foreach ($imageFiles as $imageFile) {
                $productImage = new ProductImage();
                $productImage->setImageFile($imageFile);
                $productImage->setIsMain(false);
                $product->addProductImage($productImage);
            }

            $entityManager->flush();

            // Redirige vers la page images couleurs
            $this->addFlash('success', 'Produit mis à jour. Ajoutez maintenant les images par couleur si besoin.');
            return $this->redirectToRoute('admin_product_color_images', [
                'slug' => $product->getSlug()
            ]);
        }

        return $this->render('admin/product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }
}