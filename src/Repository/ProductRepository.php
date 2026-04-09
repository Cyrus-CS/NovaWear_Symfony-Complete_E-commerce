<?php

namespace App\Repository;

// use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }
    public function paginateProduct(PaginatorInterface $paginator, int $page, int $limit = 12)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC');
        return $paginator->paginate($queryBuilder, $page, $limit);
    }

    public function newArrivalsProducts(int $limit = 8): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.isActive = true')
            ->orderBy('p.createdAt', 'DESC') // createdAt = plus récent en premier
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByCategory($category)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.category = :category')
            ->setParameter('category', $category)
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findBestSellers(int $limit){
        return $this->createQueryBuilder('p')
            ->where('p.isActive = true')
            ->orderBy('p.salesCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.categories', 'cat')
            ->leftJoin('p.brand', 'b')
            ->leftJoin('p.sizes', 's')
            ->leftJoin('p.colors', 'col')
            ->leftJoin('p.colorVariants', 'cv')
            ->where('p.isActive = true')
            ->addSelect('cat', 'b', 's', 'col');

        // Catégorie
        if (!empty($filters['category'])) {
            $qb->andWhere('cat.id = :category')
            ->setParameter('category', (int) $filters['category']);
        }

        // Marque
        if (!empty($filters['brand'])) {
            $qb->andWhere('b.id = :brand')
            ->setParameter('brand', (int) $filters['brand']);
        }

        // Tailles
        if (!empty($filters['sizes'])) {
            $qb->andWhere('s.id IN (:sizes)')
            ->setParameter('sizes', array_map('intval', $filters['sizes']));
        }

        // Couleurs
        if (!empty($filters['colors'])) {
            $qb->andWhere('col.id IN (:colors)')
            ->setParameter('colors', array_map('intval', $filters['colors']));
        }

        // Prix min
        if ($filters['min_price'] !== null && $filters['min_price'] !== '') {
            $qb->andWhere('p.price >= :min_price')
            ->setParameter('min_price', (float) $filters['min_price']);
        }

        // Prix max
        if ($filters['max_price'] !== null && $filters['max_price'] !== '') {
            $qb->andWhere('p.price <= :max_price')
            ->setParameter('max_price', (float) $filters['max_price']);
        }

        // En solde
        if (!empty($filters['on_sale'])) {
            $qb->andWhere('p.compareAtPrice IS NOT NULL')
            ->andWhere('p.compareAtPrice > p.price');
        }

        // En stock
        if (!empty($filters['in_stock'])) {
            $qb->andWhere('p.stock > 0');
        }

        // Recherche texte
        if (!empty($filters['search'])) {
            $qb->andWhere('p.name LIKE :search OR p.description LIKE :search')
            ->setParameter('search', '%' . $filters['search'] . '%');
        }

        // Tri
        match ($filters['sort'] ?? 'newest') {
            'price_asc'   => $qb->orderBy('p.price', 'ASC'),
            'price_desc'  => $qb->orderBy('p.price', 'DESC'),
            'popularity'  => $qb->orderBy('p.salesCount', 'DESC'),
            'rating'      => $qb->orderBy('p.rating_average', 'DESC'),
            default       => $qb->orderBy('p.createdAt', 'DESC'),
        };

        return $qb->distinct()->getQuery()->getResult();
    }

    public function getPriceRange(): array
    {
        $result = $this->createQueryBuilder('p')
            ->select('MIN(p.price) as min, MAX(p.price) as max')
            ->where('p.isActive = true')
            ->getQuery()
            ->getSingleResult();

        return [
            'min' => (float) ($result['min'] ?? 0),
            'max' => (float) ($result['max'] ?? 1000),
        ];
    }

    // Meilleures ventes par catégorie (jeans, montres, chaussures…)
    /*public function findBestSellersByCategory(Category $category, int $limit = 8): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.category = :category')
            ->andWhere('p.isActive = true')
            ->setParameter('category', $category)
            ->orderBy('p.salesCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }*/

    // Meilleures ventes sur les 30 derniers jours (plus précis)
    /*public function findTrendingProducts(int $limit = 8): array
    {
        $since = new \DateTimeImmutable('-30 days');

        return $this->createQueryBuilder('p')
            ->join('p.orderItems', 'oi')
            ->join('oi.order', 'o')
            ->where('o.status = :status')
            ->andWhere('o.createdAt >= :since')
            ->setParameter('status', 'paid')
            ->setParameter('since', $since)
            ->groupBy('p.id')
            ->orderBy('SUM(oi.quantity)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }*/

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}