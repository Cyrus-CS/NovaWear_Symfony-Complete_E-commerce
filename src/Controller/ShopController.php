<?php
namespace App\Controller;

use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\ColorRepository;
use App\Repository\ProductRepository;
use App\Repository\SizeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ShopController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository  $productRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly BrandRepository    $brandRepository,
        private readonly SizeRepository     $sizeRepository,
        private readonly ColorRepository    $colorRepository,
    ) {}

    #[Route('/shop', name: 'shop.index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $filters = $this->extractFilters($request);

        $products   = $this->productRepository->findByFilters($filters);
        $categories = $this->categoryRepository->findAll();
        $brands     = $this->brandRepository->findAll();
        $sizes      = $this->sizeRepository->findAll();
        $colors     = $this->colorRepository->findAll();
        $priceRange = $this->productRepository->getPriceRange();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'html'  => $this->renderView('shop/_products_grid.html.twig', [
                    'products' => $products,
                ]),
                'count' => count($products),
            ]);
        }

        return $this->render('shop/index.html.twig', [
            'products'   => $products,
            'categories' => $categories,
            'brands'     => $brands,
            'sizes'      => $sizes,
            'colors'     => $colors,
            'priceRange' => $priceRange,
            'filters'    => $filters,
        ]);
    }

    private function extractFilters(Request $request): array
    {
        return [
            'category'   => $request->query->get('category'),
            'brand'      => $request->query->get('brand'),
            'sizes'      => $request->query->all('sizes'),
            'colors'     => $request->query->all('colors'),
            'min_price'  => $request->query->get('min_price'),
            'max_price'  => $request->query->get('max_price'),
            'on_sale'    => $request->query->getBoolean('on_sale'),
            'in_stock'   => $request->query->getBoolean('in_stock'),
            'sort'       => $request->query->get('sort', 'newest'),
            'search'     => $request->query->get('search'),
        ];
    }
}