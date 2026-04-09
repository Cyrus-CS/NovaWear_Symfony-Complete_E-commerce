<?php

namespace App\Controller;

use App\Entity\Review;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReviewController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProductRepository      $productRepository,
    ) {}

    /**
     * POST /product/{id}/review
     * Accepte application/x-www-form-urlencoded (formulaire classique) ET XMLHttpRequest (AJAX).
     * Retourne du JSON si AJAX, sinon redirige vers la page produit.
     */
    #[Route('/product/{id}/review', name: 'review_store', methods: ['POST'])]
    public function store(Request $request, Product $product): Response
    {
        // ── Vérification : utilisateur connecté obligatoire ──────────
        if (!$this->getUser()) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(
                    ['error' => 'You must be logged in to post a review.'],
                    Response::HTTP_UNAUTHORIZED
                );
            }
            $this->addFlash('error', 'You must be logged in to post a review.');
            return $this->redirectToRoute('app_login');
        }
        
        // ── Vérification CSRF ──────────────────────────────────
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('review_' . $product->getId(), $token)) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['error' => 'Invalid CSRF token.'], Response::HTTP_FORBIDDEN);
            }
            $this->addFlash('error', 'Invalid security token.');
            return $this->redirectToRoute('product.show', ['slug' => $product->getSlug()]);
        }

        // ── Validation basique ─────────────────────────────────
        $authorName = trim((string) $request->request->get('author_name', ''));
        $comment    = trim((string) $request->request->get('comment', ''));
        $rating     = (int) $request->request->get('rating', 0);

        $errors = [];
        if (strlen($authorName) < 2) $errors[] = 'Name must be at least 2 characters.';
        if (strlen($comment) < 10)   $errors[] = 'Review must be at least 10 characters.';
        if ($rating < 1 || $rating > 5) $errors[] = 'Please select a rating between 1 and 5.';

        if ($errors) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            foreach ($errors as $e) $this->addFlash('error', $e);
            return $this->redirectToRoute('product.show', ['slug' => $product->getSlug()]);
        }

        // ── Création de la review ──────────────────────────────
        $review = new Review();
        $review->setProduct($product);
        $review->setAuthorName($authorName);
        $review->setComment($comment);
        $review->setRating($rating);
        $review->setIsVerified(false);
        $review->setIsPublished(true); // publié immédiatement (à modérer si besoin)
        $review->setCreatedAt(new \DateTimeImmutable());

        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if ($user) {
            $review->setUser($user);
        }

        $this->em->persist($review);

        // ── Recalcul du rating moyen du produit ────────────────
        $this->recalculateRating($product);

        $this->em->flush();

        // ── Réponse ────────────────────────────────────────────
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success'    => true,
                'message'    => 'Thank you for your review!',
                'review'     => [
                    'id'         => $review->getId(),
                    'authorName' => $review->getAuthorName(),
                    'rating'     => $review->getRating(),
                    'comment'    => $review->getComment(),
                    'createdAt'  => $review->getCreatedAt()->format('F d, Y'),
                    'isVerified' => $review->isVerified(),
                ],
            ]);
        }

        $this->addFlash('success', 'Your review has been submitted!');
        return $this->redirectToRoute('product.show', ['slug' => $product->getSlug()]);
    }

    /**
     * Recalcule ratingAverage et ratingCount sur le produit.
     */
    private function recalculateRating(Product $product): void
    {
        $reviews = $product->getReviews()->filter(fn(Review $r) => $r->isPublished());
        $count   = $reviews->count();

        if ($count === 0) {
            $product->setRatingAverage(null);
            $product->setRatingCount(0);
            return;
        }

        $sum = 0;
        foreach ($reviews as $r) {
            $sum += $r->getRating();
        }

        $product->setRatingAverage((string) round($sum / $count, 1));
        $product->setRatingCount($count);
    }
}