<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'home')]
    public function home(
        ProductRepository  $pr,
        CategoryRepository $categoryRepository,
    ): Response {
        $newsArrivals = $pr->newArrivalsProducts(8); // 8 premiers
        $topSelling = $pr->findBestSellers(20); // charge 20, le slider navigue item par item
        $categories   = $categoryRepository->findBy(['isActive' => true] ?? [], ['name' => 'ASC']);
        // Si pas de champ isActive sur Category, utilise : findAll()

        return $this->render('home/index.html.twig', [
            'newsArrivals' => $newsArrivals,
            'categories'   => $categories,
            'topSelling'   => $topSelling,
        ]);
    }

    /**
     * AJAX — charge les 4 produits suivants (page 2 des new arrivals)
     */
    #[Route('/home/more-arrivals', name: 'home.more_arrivals', methods: ['GET'])]
    public function moreArrivals(ProductRepository $pr): JsonResponse
    {
        $products = $pr->newArrivalsProducts(12); // On prend 12, slice les 4 derniers côté serveur
        $extra    = array_slice($products, 8);    // positions 8 → 11

        $data = [];
        foreach ($extra as $product) {
            // Image principale
            $mainImage      = null;
            $secondaryImage = null;

            foreach ($product->getProductImages() as $img) {
                if ($img->isMain() && $img->getColor() === null && $mainImage === null) {
                    $mainImage = $img->getPath();
                }
            }
            // Première image secondaire (non-main, sans couleur)
            foreach ($product->getProductImages() as $img) {
                if (!$img->isMain() && $img->getColor() === null && $secondaryImage === null) {
                    $secondaryImage = $img->getPath();
                }
            }

            $data[] = [
                'name'           => $product->getName(),
                'slug'           => $product->getSlug(),
                'price'          => $product->getPrice(),
                'compareAtPrice' => $product->getCompareAtPrice(),
                'ratingAverage'  => $product->getRatingAverage(),
                'ratingCount'    => $product->getRatingCount(),
                'mainImage'      => $mainImage
                    ? '/uploads/images/products/' . $mainImage
                    : '/images/placeholder.jpg',
                'secondaryImage' => $secondaryImage
                    ? '/uploads/images/products/' . $secondaryImage
                    : null,
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->redirectToRoute('home');
    }
}