<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function add(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function paginate($page, $pageSize)
    {
        $qb = $this->createQueryBuilder('p')
            ->setMaxResults($pageSize)
            ->setFirstResult(($page - 1) * $pageSize)
        ;

        return $qb->getQuery()->getResult();
    }

    public function getTotalCount(): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function paginateByCategory(int $categoryId, int $page, int $pageSize)
    {
        $qb = $this->createQueryBuilder('p')
            ->join('p.productCategories', 'pc')
            ->andWhere('pc.category = :categoryId')
            ->setParameter('categoryId', $categoryId)
            ->setMaxResults($pageSize)
            ->setFirstResult(($page - 1) * $pageSize)
        ;

        return $qb->getQuery()->getResult();
    }

    public function getTotalCountInCategory(int $categoryId): ?int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p.id) as total')
            ->join('p.productCategories', 'pc')
            ->andWhere('pc.category = :categoryId')
            ->setParameter('categoryId', $categoryId)
        ;
    
        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function filterAndSortProducts(
        int $page,
        int $pageSize,
        string $sortBy,
        string $sortOrder,
        ?string $filterByName = null,
        ?string $filterByCategory = null,
        ?string $filterByMaxPrice = null,
        ?string $filterByMinPrice = null
    ): array 
    {

        $qb = $this->createQueryBuilder('p')
            ->setMaxResults($pageSize)
            ->setFirstResult(($page - 1) * $pageSize)
            ->orderBy("p.$sortBy", $sortOrder)
        ;

        if ($filterByName) {
            $qb->andWhere('p.name LIKE :name')
                ->setParameter('name', '%' . $filterByName . '%')
            ;
        }

        if ($filterByCategory) {
            $qb
                ->join('p.productCategories', 'pc')
                ->join('pc.category', 'c')
                ->andWhere('c.name = :categoryName')
                ->setParameter('categoryName', $filterByCategory)
            ;
        }

        if ($filterByMaxPrice) {
            $qb->andWhere('p.netPrice <= :maxPrice')
                ->setParameter('maxPrice', floatval($filterByMaxPrice))
            ;
        }

        if ($filterByMinPrice) {
            $qb->andWhere('p.netPrice >= :minPrice')
                ->setParameter('minPrice', floatval($filterByMinPrice))
            ;
        }

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    public function getTotalFilteredCount(
        ?string $filterByName = null,
        ?string $filterByCategory = null,
        ?string $filterByMaxPrice = null,
        ?string $filterByMinPrice = null
    ): int 
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
        ;

        if ($filterByName) {
            $qb->andWhere('p.name LIKE :name')
                ->setParameter('name', '%' . $filterByName . '%')
            ;
        }

        if ($filterByCategory) {
            $qb
                ->join('p.productCategories', 'pc')
                ->join('pc.category', 'c')
                ->andWhere('c.name = :categoryName')
                ->setParameter('categoryName', $filterByCategory)
            ;
        }

        if ($filterByMaxPrice) {
            $qb->andWhere('p.netPrice <= :maxPrice')
                ->setParameter('maxPrice', floatval($filterByMaxPrice))
            ;
        }

        if ($filterByMinPrice) {
            $qb->andWhere('p.netPrice >= :minPrice')
                ->setParameter('minPrice', floatval($filterByMinPrice))
            ;
        }

        $result = (int) $qb->getQuery()->getSingleScalarResult();

        return $result;
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
