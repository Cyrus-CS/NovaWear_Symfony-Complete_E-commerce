<?php
// src/Controller/AccountController.php
namespace App\Controller;

use App\Entity\Address;
use App\Entity\Order;
use App\Entity\User;
use App\Entity\Wishlist;
use App\Repository\ProductRepository;
// use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/account', name: 'account.')]
final class AccountController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    #[Route('', name: 'dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        return $this->render('account/dashboard.html.twig');
    }

    #[Route('/orders', name: 'orders', methods: ['GET'])]
    public function orders(): Response
    {
        $orders = $this->em->getRepository(Order::class)->findBy(
            ['user' => $this->getUser()],
            ['createdAt' => 'DESC']
        );
        return $this->render('account/orders.html.twig', ['orders' => $orders]);
    }

    #[Route('/orders/{number}', name: 'order_show', methods: ['GET'])]
    public function orderShow(string $number): Response
    {
        $order = $this->em->getRepository(Order::class)->findOneBy([
            'number' => $number,
            'user'   => $this->getUser(),
        ]);
        if (!$order) throw $this->createNotFoundException('Order not found.');
        return $this->render('account/order_show.html.twig', ['order' => $order]);
    }

    #[Route('/addresses', name: 'addresses', methods: ['GET'])]
    public function addresses(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        return $this->render('account/addresses.html.twig', [
            'addresses' => $this->em->getRepository(Address::class)->findBy(['user' => $user]),
        ]);
    }

    #[Route('/addresses/{id}/delete', name: 'address_delete', methods: ['POST'])]
    public function deleteAddress(int $id): Response
    {
        $address = $this->em->getRepository(Address::class)->find($id);
        if ($address && $address->getUser() === $this->getUser()) {
            $this->em->remove($address);
            $this->em->flush();
            $this->addFlash('success', 'Address deleted.');
        }
        return $this->redirectToRoute('account.addresses');
    }

    #[Route('/details', name: 'details', methods: ['GET', 'POST'])]
    public function details(Request $request): Response
    {
        /** @var \App\Entity\User $user */
        $user   = $this->getUser();
        $errors = [];

        if ($request->isMethod('POST')) {
            $username = trim($request->request->get('username', ''));
            $email    = trim($request->request->get('email', ''));

            if (strlen($username) < 2) $errors[] = 'Display name must be at least 2 characters.';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';

            if (!$errors) {
                $user->setUsername($username);
                $user->setEmail($email);
                $this->em->flush();
                $this->addFlash('success', 'Account details updated.');
                return $this->redirectToRoute('account.details');
            }
        }

        return $this->render('account/details.html.twig', ['errors' => $errors]);
    }

    /* ── WISHLIST ──────────────────────────────────────────── */

    #[Route('/wishlist', name: 'wishlist', methods: ['GET'])]
    public function wishlist(): Response
    {
        $items = $this->em->getRepository(Wishlist::class)->findBy(
            ['user' => $this->getUser()],
            ['createdAt' => 'DESC']
        );
        return $this->render('account/wishlist.html.twig', ['items' => $items]);
    }

    #[Route('/wishlist/add/{id}', name: 'wishlist_add', methods: ['POST'])]
    public function wishlistAdd(int $id, ProductRepository $pr): JsonResponse
    {
        $product = $pr->find($id);
        if (!$product) return new JsonResponse(['error' => 'Product not found'], 404);

        $existing = $this->em->getRepository(Wishlist::class)->findOneBy([
            'user'    => $this->getUser(),
            'product' => $product,
        ]);

        if (!$existing) {
            $wl = new Wishlist();
            $wl->setUser($this->getUser());
            $wl->setProduct($product);
            $this->em->persist($wl);
            $this->em->flush();
        }

        return new JsonResponse(['success' => true]);
    }

    #[Route('/wishlist/remove/{id}', name: 'wishlist_remove', methods: ['POST'])]
    public function wishlistRemove(int $id): JsonResponse
    {
        $wl = $this->em->getRepository(Wishlist::class)->find($id);
        if ($wl && $wl->getUser() === $this->getUser()) {
            $this->em->remove($wl);
            $this->em->flush();
        }
        return new JsonResponse(['success' => true]);
    }
}