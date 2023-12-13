<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ContractList;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\ContractListRepository;
use App\Repository\ProductPriceListRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

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
            'pageSize' => $pageSize,
            'totalItems' =>  $totalCount,
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
            'pageSize' => $pageSize,
            'totalItems' =>  $totalCount,
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
     * @Route("/filtered-products", name="filtered_products", methods={"GET"})
     */
    public function filteredProducts(Request $request, 
        ProductRepository $productRepository, 
        ProductPriceListRepository $productPriceListRepository, 
        ContractListRepository $contractListRepository): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $pageSize = $request->query->getInt('pageSize', 10);
        $sortBy = $request->query->get('sortBy', 'name'); // Default sort by name
        $sortOrder = $request->query->get('sortOrder', 'asc'); // Default sort order ASC
        $filterByName = $request->query->get('name');
        $filterByCategory = $request->query->get('category');
        $filterByMaxPrice = $request->query->get('maxPrice');
        $filterByMinPrice = $request->query->get('minPrice');

        $paginator = $productRepository->filterAndSortProducts(
            $page,
            $pageSize,
            $sortBy,
            $sortOrder,
            $filterByName,
            $filterByCategory,
            $filterByMaxPrice,
            $filterByMinPrice
        );

        $totalCount = $productRepository->getTotalFilteredCount(
            $filterByName,
            $filterByCategory,
            $filterByMaxPrice,
            $filterByMinPrice,
        );

        $data = [
            'items' => [],
            'page' => $page,
            'pageSize' => $pageSize,
            'totalItems' =>  $totalCount,
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
     * @Route("/orders/new", name="create_order", methods={"POST"})
     */
    public function createOrder(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        if (!$requestData || !isset($requestData['user_id']) || !isset($requestData['products'])) {
            return $this->json(['message' => 'Invalid request data'], 400);
        }

        $userId = $requestData['user_id'];
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $userId]);

        if (!$user) {
            return $this->json(['message' => 'User not found'], 404);
        }

        $order = new Order();
        $order->setOrderDate(new \DateTime());
        $order->setUser($user);

        $totalPrice = 0;

        foreach ($requestData['products'] as $productData) {
            $vatPercentage = 25;
            $discountPercentage = 10;

            $productId = $productData['product_id'];
            $product = $productRepository->findOneBy(['id' => $productId]);

            if (!$product) {
                return $this->json(['message' => 'Product not found'], 404);
            }

            $quantity = $productData['quantity'];
            $netPrice = $this->getContractListPrice($product, $user);
            $vatValue = $netPrice * $vatPercentage / 100;
            $unitPrice = strval($netPrice + $vatValue);

            $orderProduct = new OrderProduct();
            $orderProduct->setProduct($product);
            $orderProduct->setOrder($order);
            $orderProduct->setQuantity($quantity);
            $orderProduct->setUnitPrice(strval($unitPrice));
            $orderProduct->setVat($vatPercentage);

            $this->entityManager->persist($orderProduct);

            $totalPrice += $quantity * $unitPrice;
        }

        if ($totalPrice >= 100) {
            $discountValue = $totalPrice * $discountPercentage / 100;
            $discountedPrice = $totalPrice - $discountValue;

            $order->setTotalPrice(strval($discountedPrice));
            $order->setDiscount($discountPercentage);
        } else {
            $order->setTotalPrice(strval($totalPrice));
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $this->json(['message' => 'Order created successfully'], 201);
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

    function getContractListPrice(Product $product, User $user): string {
        $contractListRepository = $this->entityManager->getRepository(ContractList::class);
        $contractListProduct = $contractListRepository->findBy([
            'product' => $product,
            'user' => $user,
        ]);

        if (!empty($contractListProduct)) {
            $contractNetPrice = $contractListProduct[0]->getPrice();

            return $contractNetPrice;
        } else {
            return $product->getNetPrice();
        }
    }
}