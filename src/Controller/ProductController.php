<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    /**
     * @Route("/products", name="products_list", methods={"GET"})
     */
    public function products(Request $request, ProductRepository $repository): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('pageSize', 10);

        $paginator = $repository->paginate($page, $pageSize);
        $totalCount = $repository->getTotalCount();

        $data = [
            'items' => [],
            'page' => $page,
            'totalItems' =>  $totalCount,
            'pageSize' => $pageSize,
        ];

        foreach ($paginator as $product) {
            $categories = $this->getProductCategories($product);

            $data['items'][] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'SKU' => $product->getSKU(),
                'netPrice' => $product->getNetPrice(),
                'published' => $product->isPublished(),
                'categories' => $categories,
            ];
        }

        return $this->json($data);
    }

    /**
     * @Route("/products/{id}", name="product_show", methods={"GET"})
     */
    public function showProduct(int $productId, ProductRepository $repository): JsonResponse
    {
        $product = $repository->find($productId);

        if (!$product) {
            return $this->json(['message' => 'Product not found'], 404);
        }

        $categories = $this->getProductCategories($product);

        $data = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'SKU' => $product->getSKU(),
            'netPrice' => $product->getNetPrice(),
            'published' => $product->isPublished(),
            'categories' => $categories
        ];

        return $this->json($data);
    }

    /**
     * @Route("/category/{categoryId}/products", name="products_in_category", methods={"GET"})
     */
    public function productsInCategory(int $categoryId, Request $request, ProductRepository $productRepository): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('pageSize', 10);

        $paginator = $productRepository->paginateByCategory($categoryId, $page, $pageSize);
        $totalCount = $productRepository->getTotalCountInCategory($categoryId);

        $data = [
            'items' => [],
            'page' => $page,
            'totalItems' =>  $totalCount,
            'pageSize' => $pageSize,
        ];

        foreach ($paginator as $product) {
            $categories = $this->getProductCategories($product);

            $data['items'][] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'SKU' => $product->getSKU(),
                'netPrice' => $product->getNetPrice(),
                'published' => $product->isPublished(),
                'categories' => $categories,
            ];
        }

        return $this->json($data);
    }

    function getProductCategories(object $product): ?array
    {
        $categories = [];

        $productCategories = $product->getProductCategories()->toArray();

        foreach ($productCategories as $item) {
            $category = $item->getCategory();

            $categories[] = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'mainCategory' => [
                    'id' => $category->getMainCategory()->getId(),
                    'name' => $category->getMainCategory()->getName(),
                ],
            ];
        }

        return $categories;
    }
}