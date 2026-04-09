<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartItem;
use App\Repository\CartRepository;
use App\Repository\CouponRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CartController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly CartRepository $cartRepository,
        private readonly CouponRepository $couponRepository,
        private readonly EntityManagerInterface $em,
    ) {}

    private function getCurrentCart(SessionInterface $session): ?Cart
    {
        $user = $this->getUser();

        if ($user) {
            return $this->cartRepository->findOneBy(['user' => $user]);
        }

        $cartId = $session->get('cart_id');
        if ($cartId) {
            return $this->cartRepository->find($cartId);
        }

        return null;
    }

    private function getOrCreateCart(SessionInterface $session): Cart
    {
        $cart = $this->getCurrentCart($session);
        if ($cart) {
            return $cart;
        }

        $cart = new Cart();
        $cart->setCreatedAt(new \DateTimeImmutable());
        $cart->setUpdatedAt(new \DateTimeImmutable());

        $user = $this->getUser();
        if ($user) {
            $cart->setUser($user);
        }

        $this->em->persist($cart);
        $this->em->flush();

        if (!$user) {
            $session->set('cart_id', $cart->getId());
        }

        return $cart;
    }

    /**
     * Calcule les totaux du panier et retourne un tableau.
     */
    private function computeTotals(Cart $cart): array
    {
        $subtotal = 0.0;

        foreach ($cart->getCartItems() as $cartItem) {
            $subtotal += (float) $cartItem->getUnitPrice() * $cartItem->getQuantity();
        }

        $discount = 0.0;
        $coupon = $cart->getCoupon();

        if ($coupon && $coupon->isActive()) {
            $now = new \DateTimeImmutable();
            $validStart  = $coupon->getStartsAt() === null || $coupon->getStartsAt() <= $now;
            $validExpiry = $coupon->getExpiresAt() === null || $coupon->getExpiresAt() >= $now;
            $validMin    = $subtotal >= (float) $coupon->getMinCartTotal();
            $validUses   = $coupon->getMaxUses() === null || $coupon->getUsedCount() < $coupon->getMaxUses();

            if ($validStart && $validExpiry && $validMin && $validUses) {
                if ($coupon->getDiscountType() === 'percent') {
                    $discount = $subtotal * ((float) $coupon->getDiscountValue() / 100);
                } else {
                    $discount = min((float) $coupon->getDiscountValue(), $subtotal);
                }
            }
        }

        $total = $subtotal - $discount + 15.0; // delivery fee

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'total'    => round($total, 2),
        ];
    }

    #[Route('/cart', name: 'cart.index', methods: ['GET'])]
    public function index(SessionInterface $session): Response
    {
        $cart = $this->getCurrentCart($session);

        if (!$cart) {
            return $this->render('cart/index.html.twig', [
                'items'    => [],
                'subtotal' => 0,
                'discount' => 0,
                'total'    => 0,
                'cart'     => null,
            ]);
        }

        $items = $this->buildItemsArray($cart);
        $totals = $this->computeTotals($cart);

        return $this->render('cart/index.html.twig', array_merge([
            'cart'  => $cart,
            'items' => $items,
        ], $totals));
    }

    /**
     * Construit le tableau d'items pour la vue, avec résolution d'image correcte.
     */
    private function buildItemsArray(Cart $cart): array
    {
        $items = [];

        foreach ($cart->getCartItems() as $cartItem) {
            $product = $cartItem->getProduct();
            if (!$product) {
                continue;
            }

            $variant   = $cartItem->getVariant();
            $size      = $cartItem->getSize();
            $qty       = $cartItem->getQuantity();
            $unit      = (float) $cartItem->getUnitPrice();
            $lineTotal = $unit * $qty;

            // Résolution d'image : priorité à l'image principale de la couleur du variant
            $image = null;
            if ($variant && $variant->getColor()) {
                foreach ($product->getProductImages() as $img) {
                    if (
                        $img->getColor() &&
                        $img->getColor()->getId() === $variant->getColor()->getId() &&
                        $img->isMain()
                    ) {
                        $image = $img;
                        break;
                    }
                }
                // Fallback : n'importe quelle image de cette couleur
                if (!$image) {
                    foreach ($product->getProductImages() as $img) {
                        if ($img->getColor() && $img->getColor()->getId() === $variant->getColor()->getId()) {
                            $image = $img;
                            break;
                        }
                    }
                }
            }

            // Fallback final : image principale du produit
            if (!$image) {
                $image = $product->getMainImage();
            }

            $items[] = [
                'entity'    => $cartItem,
                'product'   => $product,
                'variant'   => $variant,
                'size'      => $size,
                'image'     => $image,
                'quantity'  => $qty,
                'unitPrice' => $unit,
                'lineTotal' => $lineTotal,
            ];
        }

        return $items;
    }

    #[Route('/cart/add/{slug}', name: 'cart.add', methods: ['POST', 'GET'], requirements: ['slug' => '[a-z0-9\-]+'])]
    public function addToCart(string $slug, Request $request, SessionInterface $session): Response
    {
        $product = $this->productRepository->findOneBy(['slug' => $slug]);
        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        $cart = $this->getOrCreateCart($session);

        $qty = max(1, (int) $request->request->get('quantity', 1));

        // Dans addToCart(), remplace le bloc variant/size par :

        $variant = null;
        $size    = null;

        $variantId = $request->request->get('variant_id');
        $sizeId    = $request->request->get('size_id');

        // variant_id correspond à color.id → on cherche le ProductColorVariant lié à ce produit+couleur
        if ($variantId) {
            $variant = $this->em->getRepository(\App\Entity\ProductColorVariant::class)
                ->findOneBy([
                    'product' => $product,
                    'color'   => (int) $variantId,
                ]);
        }

        if ($sizeId) {
            $size = $this->em->getRepository(\App\Entity\Size::class)->find((int) $sizeId);
        }

        $cartItemRepo = $this->em->getRepository(CartItem::class);
        /** @var CartItem|null $cartItem */
        $cartItem = $cartItemRepo->findOneBy([
            'cart'    => $cart,
            'product' => $product,
            'variant' => $variant,
            'size'    => $size,
        ]);

        if ($cartItem) {
            $cartItem->setQuantity($cartItem->getQuantity() + $qty);
        } else {
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setProduct($product);
            $cartItem->setVariant($variant);
            $cartItem->setSize($size);

            $unitPrice = $product->getPrice();
            if ($variant && $variant->getPrice() !== null) {
                $unitPrice = $variant->getPrice();
            }
            $cartItem->setUnitPrice((string) $unitPrice);
            $cartItem->setQuantity($qty);

            $this->em->persist($cartItem);
        }

        $cart->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();

        return $this->redirectToRoute('cart.index');
    }

    /**
     * AJAX — Met à jour la quantité d'un item (+1 ou -1).
     * Si quantité tombe à 0, supprime l'item.
     */
    #[Route('/cart/item/{id}/update', name: 'cart.item_update', methods: ['POST'])]
    public function updateItem(int $id, Request $request, SessionInterface $session): JsonResponse
    {
        $cart = $this->getCurrentCart($session);
        if (!$cart) {
            return new JsonResponse(['error' => 'Panier introuvable'], 404);
        }

        $cartItem = $this->em->getRepository(CartItem::class)->find($id);
        if (!$cartItem || $cartItem->getCart() !== $cart) {
            return new JsonResponse(['error' => 'Item introuvable'], 404);
        }

        $data  = json_decode($request->getContent(), true) ?? [];
        $delta = (int) ($data['delta'] ?? 0);

        $newQty = $cartItem->getQuantity() + $delta;

        // Minimum 1 — on ne supprime jamais via ce endpoint
        if ($newQty < 1) {
            $newQty = 1;
        }

        $cartItem->setQuantity($newQty);
        $cart->setUpdatedAt(new \DateTimeImmutable());
        $this->em->flush();

        $totals = $this->computeTotals($cart);

        return new JsonResponse(array_merge([
            'quantity' => $newQty,
            'removed'  => false,
            'lineTotal' => round((float) $cartItem->getUnitPrice() * $newQty, 2),
        ], $totals));
    }

    /**
     * AJAX — Supprime un item du panier.
     */
    #[Route('/cart/item/{id}/delete', name: 'cart.item_delete', methods: ['POST'])]
    public function deleteItem(int $id, SessionInterface $session): JsonResponse
    {
        $cart = $this->getCurrentCart($session);
        if (!$cart) {
            return new JsonResponse(['error' => 'Panier introuvable'], 404);
        }

        $cartItem = $this->em->getRepository(CartItem::class)->find($id);
        if ($cartItem && $cartItem->getCart() === $cart) {
            $this->em->remove($cartItem);
            $cart->setUpdatedAt(new \DateTimeImmutable());
            $this->em->flush();
        }

        $totals = $this->computeTotals($cart);

        return new JsonResponse(array_merge(['removed' => true], $totals));
    }

    /**
     * Route legacy (non-AJAX) — conservée pour rétrocompatibilité.
     */
    #[Route('/cart/item/{id}/remove', name: 'cart.item_remove', methods: ['POST', 'GET'])]
    public function removeFromCart(int $id, SessionInterface $session): Response
    {
        $cart = $this->getCurrentCart($session);
        if (!$cart) {
            return $this->redirectToRoute('cart.index');
        }

        $cartItem = $this->em->getRepository(CartItem::class)->find($id);
        if ($cartItem && $cartItem->getCart() === $cart) {
            $this->em->remove($cartItem);
            $cart->setUpdatedAt(new \DateTimeImmutable());
            $this->em->flush();
        }

        return $this->redirectToRoute('cart.index');
    }

    /**
     * AJAX — Applique ou retire un coupon sur le panier.
     */
    #[Route('/cart/coupon/apply', name: 'cart.coupon_apply', methods: ['POST'])]
    public function applyCoupon(Request $request, SessionInterface $session): JsonResponse
    {
        $cart = $this->getCurrentCart($session);
        if (!$cart) {
            return new JsonResponse(['error' => 'Panier introuvable'], 404);
        }

        $data = json_decode($request->getContent(), true) ?? [];
        $code = trim((string) ($data['code'] ?? ''));

        // Retirer le coupon si code vide
        if ($code === '') {
            $cart->setCoupon(null);
            $cart->setDiscountAmount(null);
            $this->em->flush();

            $totals = $this->computeTotals($cart);
            return new JsonResponse(array_merge(['success' => false, 'message' => 'Coupon retiré.'], $totals));
        }

        $coupon = $this->couponRepository->findOneBy(['code' => $code]);

        if (!$coupon) {
            return new JsonResponse(['error' => 'Code promo invalide.'], 422);
        }

        if (!$coupon->isActive()) {
            return new JsonResponse(['error' => 'Ce code promo n\'est plus actif.'], 422);
        }

        $now = new \DateTimeImmutable();

        if ($coupon->getStartsAt() !== null && $coupon->getStartsAt() > $now) {
            return new JsonResponse(['error' => 'Ce code promo n\'est pas encore valide.'], 422);
        }

        if ($coupon->getExpiresAt() !== null && $coupon->getExpiresAt() < $now) {
            return new JsonResponse(['error' => 'Ce code promo a expiré.'], 422);
        }

        if ($coupon->getMaxUses() !== null && $coupon->getUsedCount() >= $coupon->getMaxUses()) {
            return new JsonResponse(['error' => 'Ce code promo a atteint sa limite d\'utilisation.'], 422);
        }

        // Calculer le sous-total avant d'appliquer le coupon
        $subtotal = 0.0;
        foreach ($cart->getCartItems() as $ci) {
            $subtotal += (float) $ci->getUnitPrice() * $ci->getQuantity();
        }

        if ($subtotal < (float) $coupon->getMinCartTotal()) {
            return new JsonResponse([
                'error' => sprintf(
                    'Le montant minimum pour ce code est $%.2f.',
                    (float) $coupon->getMinCartTotal()
                ),
            ], 422);
        }

        $cart->setCoupon($coupon);
        $this->em->flush();

        $totals = $this->computeTotals($cart);
        $cart->setDiscountAmount((string) $totals['discount']);
        $this->em->flush();

        return new JsonResponse(array_merge([
            'success' => true,
            'message' => sprintf('Code "%s" appliqué avec succès !', $coupon->getCode()),
        ], $totals));
    }
}