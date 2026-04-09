<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Enum\OrderStatus;
use App\Repository\CartRepository;
use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class CheckoutController extends AbstractController
{
    public function __construct(
        private readonly CartRepository         $cartRepository,
        private readonly CountryRepository      $countryRepository,
        private readonly EntityManagerInterface $em,
    ) {}

    /* ── Helpers privés ──────────────────────────────────────────── */

    private function getCurrentCart(SessionInterface $session): ?\App\Entity\Cart
    {
        $user = $this->getUser();
        if ($user) {
            return $this->cartRepository->findOneBy(['user' => $user]);
        }
        $cartId = $session->get('cart_id');
        return $cartId ? $this->cartRepository->find($cartId) : null;
    }

    private function computeTotals(\App\Entity\Cart $cart): array
    {
        $subtotal = 0.0;
        foreach ($cart->getCartItems() as $ci) {
            $subtotal += (float) $ci->getUnitPrice() * $ci->getQuantity();
        }

        $discount = 0.0;
        $coupon   = $cart->getCoupon();
        if ($coupon && $coupon->isActive()) {
            $now = new \DateTimeImmutable();
            $ok  = ($coupon->getStartsAt()  === null || $coupon->getStartsAt()  <= $now)
                && ($coupon->getExpiresAt() === null || $coupon->getExpiresAt() >= $now)
                && ($subtotal >= (float) $coupon->getMinCartTotal())
                && ($coupon->getMaxUses() === null || $coupon->getUsedCount() < $coupon->getMaxUses());

            if ($ok) {
                $discount = $coupon->getDiscountType() === 'percent'
                    ? $subtotal * ((float) $coupon->getDiscountValue() / 100)
                    : min((float) $coupon->getDiscountValue(), $subtotal);
            }
        }

        $shipping = $subtotal >= 500 ? 0.0 : 15.0;
        $tax      = round(($subtotal - $discount) * 0.08, 2);
        $total    = $subtotal - $discount + $shipping + $tax;

        return [
            'subtotal' => round($subtotal, 2),
            'discount' => round($discount, 2),
            'shipping' => round($shipping, 2),
            'tax'      => round($tax, 2),
            'total'    => round($total, 2),
        ];
    }

    private function buildItemsArray(\App\Entity\Cart $cart): array
    {
        $items = [];
        foreach ($cart->getCartItems() as $cartItem) {
            $product = $cartItem->getProduct();
            if (!$product) continue;

            $variant = $cartItem->getVariant();
            $size    = $cartItem->getSize();
            $unit    = (float) $cartItem->getUnitPrice();
            $qty     = $cartItem->getQuantity();

            $image = null;
            if ($variant && $variant->getColor()) {
                foreach ($product->getProductImages() as $img) {
                    if ($img->getColor()?->getId() === $variant->getColor()->getId() && $img->isMain()) {
                        $image = $img; break;
                    }
                }
                if (!$image) {
                    foreach ($product->getProductImages() as $img) {
                        if ($img->getColor()?->getId() === $variant->getColor()->getId()) {
                            $image = $img; break;
                        }
                    }
                }
            }
            if (!$image) $image = $product->getMainImage();

            $items[] = [
                'entity'    => $cartItem,
                'product'   => $product,
                'variant'   => $variant,
                'size'      => $size,
                'image'     => $image,
                'quantity'  => $qty,
                'unitPrice' => $unit,
                'lineTotal' => $unit * $qty,
            ];
        }
        return $items;
    }

    /* ── Routes ──────────────────────────────────────────────────── */

    /**
     * Affiche la page checkout.
     */
    #[Route('/checkout', name: 'checkout.index', methods: ['GET'])]
    public function index(SessionInterface $session): Response
    {
        $cart = $this->getCurrentCart($session);

        if (!$cart || $cart->getCartItems()->isEmpty()) {
            $this->addFlash('warning', 'Your cart is empty.');
            return $this->redirectToRoute('cart.index');
        }

        $totals         = $this->computeTotals($cart);
        $countries      = $this->countryRepository->findBy(['isActive' => true], ['name' => 'ASC']);
        $user = new User();
        $savedAddresses = $user? $user->getAddresses()->toArray() : [];

        return $this->render('checkout/index.html.twig', array_merge([
            'cart'           => $cart,
            'items'          => $this->buildItemsArray($cart),
            'countries'      => $countries,
            'savedAddresses' => $savedAddresses,
            // placeholder pour form_start / form_end dans le template
            'shippingForm'   => $this->createFormBuilder()->getForm()->createView(),
        ], $totals));
    }

    /**
     * Traite le formulaire et crée la commande.
     */
    #[Route('/checkout/create', name: 'order.create', methods: ['POST'])]
    public function create(Request $request, SessionInterface $session): Response
    {
        if (!$this->isCsrfTokenValid('checkout', $request->request->get('_token'))) {
            $this->addFlash('error', 'Invalid security token. Please try again.');
            return $this->redirectToRoute('checkout.index');
        }

        $cart = $this->getCurrentCart($session);
        if (!$cart || $cart->getCartItems()->isEmpty()) {
            $this->addFlash('warning', 'Your cart is empty.');
            return $this->redirectToRoute('cart.index');
        }

        // 1) Adresse ─────────────────────────────────────────
        $addrData  = $request->request->all('address');
        $countryId = (int) ($addrData['countryId'] ?? 0);
        $country   = $this->countryRepository->find($countryId);

        if (!$country) {
            $this->addFlash('error', 'Please select a valid country.');
            return $this->redirectToRoute('checkout.index');
        }

        $address = new Address();
        $address->setFirstName($addrData['firstName'] ?? '');
        $address->setLastName($addrData['lastName']   ?? '');
        $address->setStreet($addrData['street']       ?? '');
        $address->setCity($addrData['city']           ?? '');
        $address->setPostalCode($addrData['postalCode'] ?? '');
        $address->setPhone($addrData['phone']         ?: null);
        $address->setCountry($country);
        $address->setCreatedAt(new \DateTimeImmutable());

        $user = $this->getUser();
        if ($user) {
            $address->setUser($user);
        }
        $this->em->persist($address);

        // 2) Totaux ──────────────────────────────────────────
        $totals = $this->computeTotals($cart);

        // 3) Commande ────────────────────────────────────────
        $order = new Order();
        $order->setNumber('NW-' . strtoupper(substr(uniqid('', true), -8)));
        $order->setStatus(OrderStatus::Pending);
        $order->setTotalTtc((string) $totals['total']);
        $order->setTotalHt((string) $totals['subtotal']);
        $order->setCurrency('USD');
        $order->setCreatedAt(new \DateTimeImmutable());
        $order->setUpdatedAt(new \DateTimeImmutable());
        if ($user) $order->setUser($user);

        $this->em->persist($order);

        // 4) OrderItems + décrément stock ────────────────────
        foreach ($cart->getCartItems() as $cartItem) {
            $product = $cartItem->getProduct();
            if (!$product) continue;

            $item = new OrderItem();
            $item->setOrderId($order);
            $item->setProductId($product);
            $item->setProductName($product->getName());
            $item->setQuantity($cartItem->getQuantity());
            $item->setUnitPrice($cartItem->getUnitPrice());
            $item->setTotalPrice(
                (string) round((float)$cartItem->getUnitPrice() * $cartItem->getQuantity(), 2)
            );
            $this->em->persist($item);

            $product->setStock(max(0, $product->getStock() - $cartItem->getQuantity()));
        }

        // 5) Coupon — incrémenter usedCount ──────────────────
        $coupon = $cart->getCoupon();
        if ($coupon && $totals['discount'] > 0) {
            $coupon->setUsedCount(($coupon->getUsedCount() ?? 0) + 1);
        }

        // 6) Vider le panier ─────────────────────────────────
        foreach ($cart->getCartItems() as $cartItem) {
            $this->em->remove($cartItem);
        }
        $cart->setCoupon(null);
        $cart->setDiscountAmount(null);
        $cart->setUpdatedAt(new \DateTimeImmutable());

        if (!$user) {
            $session->remove('cart_id');
        }

        $this->em->flush();

        $this->addFlash('success', sprintf(
            'Order %s placed successfully! Thank you for your purchase.',
            $order->getNumber()
        ));

        return $this->redirectToRoute('order.confirmation', ['number' => $order->getNumber()]);
    }

    /**
     * Page de confirmation post-commande.
     */
    #[Route('/order/confirmation/{number}', name: 'order.confirmation', methods: ['GET'])]
    public function confirmation(string $number)
    {
        $order = $this->em->getRepository(Order::class)->findOneBy(['number' => $number]);

        if (!$order) {
            throw $this->createNotFoundException('Order not found.');
        }

        // Un invité peut voir sa confirmation (pas encore connecté) ;
        // un user connecté ne peut voir que sa propre commande
        
        $user = $this->getUser();
        if ($user && $order->getUser() && $order->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('checkout/confirmation.html.twig', [
            'order' => $order,
        ]);
    }
}