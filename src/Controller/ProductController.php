<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\City;
use App\Entity\ContractList;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Utils\ValidatorTrait;

class ProductController extends AbstractController
{
    use ValidatorTrait;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/products', name: 'products_list', methods: ['GET'])]
    public function products(Request $request, ProductRepository $repository, ValidatorInterface $validator): JsonResponse
    {
        try {
            list($page, $pageSize) = $this->pageValidator($request, $validator);
            list($userId, $priceListId) = $this->idValidator($request, $validator);
        } catch (Exception $e) {
            return new JsonResponse(json_decode($e->getMessage(), true), $e->getCode());
        }

        $paginator = $repository->paginate($page, $pageSize, $userId, $priceListId);
        $totalCount = $repository->getTotalCount($userId, $priceListId);

        $data = [
            'items' => [],
            'page' => $page,
            'pageSize' => $pageSize,
            'totalItems' =>  $totalCount,
        ];

        foreach ($paginator as $item) {
            $product = is_array($item) ? $item[0] : $item;

            $categories = $this->getProductCategories($product);
            $priceListPrices = $this->getPriceListPrices($product);
            $contractListPrices = $this->getContractListPrices($product);

            $netPrice = $product->getNetPrice();

            if ($userId) {
                $netPrice = $item['contractListPrice'] ?? $netPrice;
            } else if ($priceListId) {
                $netPrice = $item['priceListPrice'] ?? $netPrice;
            }

            $data['items'][] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'SKU' => $product->getSKU(),
                'netPrice' => $netPrice,
                'priceListPrices' => $priceListPrices,
                'contractListPrices' => $contractListPrices,
                'published' => $product->isPublished(),
                'categories' => $categories,
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/products/{productId}', name: 'product_show', methods: ['GET'])]
    public function showProduct(Request $request, string $productId, ProductRepository $repository, ValidatorInterface $validator): JsonResponse
    {
        try {
            $productId = $this->urlParamValidator($productId, $validator, 'productId');
            list($userId, $priceListId) = $this->idValidator($request, $validator);
        } catch (Exception $e) {
            return new JsonResponse(json_decode($e->getMessage(), true), $e->getCode());
        }

        $productFind = $repository->findProduct($productId, $userId, $priceListId);

        if (!$productFind) {
            return $this->json(['error' => 'Product not found.'], 404);
        }

        $product = is_array($productFind) ? $productFind[0] : $productFind;

        $netPrice = $product->getNetPrice();

        if ($userId) {
            $netPrice = $productFind['contractListPrice'] ?? $netPrice;
        } else if ($priceListId) {
            $netPrice = $productFind['priceListPrice'] ?? $netPrice;
        }

        $categories = $this->getProductCategories($product);
        $priceListPrices = $this->getPriceListPrices($product);
        $contractListPrices = $this->getContractListPrices($product);

        $data = [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'SKU' => $product->getSKU(),
            'netPrice' => $netPrice,
            'priceListPrices' => $priceListPrices,
            'contractListPrices' => $contractListPrices,
            'published' => $product->isPublished(),
            'categories' => $categories
        ];

        return new JsonResponse($data);
    }

    #[Route('/category/{categoryId}/products', name: 'products_in_category', methods: ['GET'])]
    public function productsInCategory(Request $request, string $categoryId, ProductRepository $productRepository, ValidatorInterface $validator): JsonResponse
    {
        try {
            $categoryId = $this->urlParamValidator($categoryId, $validator, 'categoryId');
            list($page, $pageSize) = $this->pageValidator($request, $validator);
            list($userId, $priceListId) = $this->idValidator($request, $validator);
        } catch (Exception $e) {
            return new JsonResponse(json_decode($e->getMessage(), true), $e->getCode());
        }

        $paginator = $productRepository->paginateByCategory($categoryId, $page, $pageSize, $userId, $priceListId);
        $totalCount = $productRepository->getTotalCountInCategory($categoryId, $userId, $priceListId);

        $data = [
            'items' => [],
            'page' => $page,
            'pageSize' => $pageSize,
            'totalItems' =>  $totalCount,
        ];

        foreach ($paginator as $item) {
            $product = is_array($item) ? $item[0] : $item;

            $categories = $this->getProductCategories($product);

            $netPrice = $product->getNetPrice();

            if ($userId) {
                $netPrice = $item['contractListPrice'] ?? $netPrice;
            } else if ($priceListId) {
                $netPrice = $item['priceListPrice'] ?? $netPrice;
            }

            $data['items'][] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'SKU' => $product->getSKU(),
                'netPrice' => $netPrice,
                'published' => $product->isPublished(),
                'categories' => $categories,
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/filtered-products', name: 'filtered_products', methods: ['GET'])]
    public function filteredProducts(Request $request, ProductRepository $productRepository, ValidatorInterface $validator): JsonResponse
    {
        try {
            list($page, $pageSize) = $this->pageValidator($request, $validator);
            list($sortBy, $sortOrder, $filterByName, $filterByCategory, $filterByMaxPrice, $filterByMinPrice) 
                = $this->productFilterValidator($request, $validator);
            list($userId, $priceListId) = $this->idValidator($request, $validator);
        } catch (Exception $e) {
            return new JsonResponse(json_decode($e->getMessage(), true), $e->getCode());
        }

        $paginator = $productRepository->filterAndSortProducts(
            $page,
            $pageSize,
            $sortBy,
            $sortOrder,
            $filterByName,
            $filterByCategory,
            $filterByMaxPrice,
            $filterByMinPrice,
            $userId,
            $priceListId
        );

        $totalCount = $productRepository->getTotalFilteredCount(
            $filterByName,
            $filterByCategory,
            $filterByMaxPrice,
            $filterByMinPrice,
            $userId,
            $priceListId
        );

        $data = [
            'items' => [],
            'page' => $page,
            'pageSize' => $pageSize,
            'totalItems' =>  $totalCount,
        ];

        foreach ($paginator as $item) {
            $product = is_array($item) ? $item[0] : $item;

            $categories = $this->getProductCategories($product);
            $priceListPrices = $this->getPriceListPrices($product);
            $contractListPrices = $this->getContractListPrices($product);

            $netPrice = $product->getNetPrice();

            if ($userId) {
                $netPrice = $item['contractListPrice'] ?? $netPrice;
            } else if ($priceListId) {
                $netPrice = $item['priceListPrice'] ?? $netPrice;
            }

            $data['items'][] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'SKU' => $product->getSKU(),
                'netPrice' => $netPrice,
                'priceListPrices' => $priceListPrices,
                'contractListPrices' => $contractListPrices,
                'published' => $product->isPublished(),
                'categories' => $categories,
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/orders/new', name: 'create_order', methods: ['POST'])]
    public function createOrder(Request $request, ProductRepository $productRepository, ValidatorInterface $validator): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        if (!$requestData || !isset($requestData['user_id']) || !isset($requestData['products'])) {
            return $this->json(['error' => 'Invalid request data.'], 400);
        }

        $userId = $requestData['user_id'];
        $address = $requestData['address'];
        $email = $requestData['email'];
        $phone = $requestData['phone'];
        $cityId = $requestData['city_id'];

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $userId]);
        $city = $this->entityManager->getRepository(City::class)->findOneBy(['id' => $cityId]);

        if (!$user) {
            return $this->json(['error' => 'User with id ' . $userId . ' not found.'], 404);
        }

        if (!$city) {
            return $this->json(['error' => 'City with id ' . $cityId . ' not found.'], 404);
        }

        $order = new Order();
        $order->setOrderDate(new \DateTime());
        $order->setUser($user);
        $order->setAddress($address);
        $order->setEmail($email);
        $order->setPhone($phone);
        $order->setCity($city);

        $totalPrice = 0;

        foreach ($requestData['products'] as $productData) {
            $vatPercentage = 25;
            $productDiscountPercentage = null;

            if (isset($productData['vat'])) {
                $vatPercentage = $productData['vat'];
            }

            if (isset($productData['discount'])) {
                $productDiscountPercentage = $productData['discount'];
            }

            $productId = $productData['product_id'];
            $product = $productRepository->findOneBy(['id' => $productId]);

            if (!$product) {
                return $this->json(['error' => 'Product with id ' . $productId . ' not found.'], 404);
            }

            $quantity = $productData['quantity'];

            $netPrice = floatval($this->getContractListPrice($product, $user));
            $vatValue = $netPrice * $vatPercentage / 100;
            $unitPrice = floatval($netPrice + $vatValue);

            if ($productDiscountPercentage !== null) {
                $discountedValue = $unitPrice * $productDiscountPercentage / 100;
                $unitPrice = $unitPrice - $discountedValue;
            }

            $orderProduct = new OrderProduct();
            $orderProduct->setProduct($product);
            $orderProduct->setOrder($order);
            $orderProduct->setQuantity($quantity);
            $orderProduct->setUnitPrice(strval($unitPrice));
            $orderProduct->setVat($vatPercentage);
            $orderProduct->setDiscount($productDiscountPercentage);

            $this->entityManager->persist($orderProduct);

            $totalPrice += $quantity * $unitPrice;
        }

        if ($totalPrice >= 100) {
            $orderDiscountPercentage = 10;

            $discountedValue = $totalPrice * $orderDiscountPercentage / 100;
            $discountedPrice = $totalPrice - $discountedValue;

            $order->setTotalPrice(strval($discountedPrice));
            $order->setDiscount($orderDiscountPercentage);
        } else {
            $order->setTotalPrice(strval($totalPrice));
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        $responseData = ['message' => 'Order created successfully'];

        return new JsonResponse($responseData, 201);
    }

    private function getProductCategories(object $product): array
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

    private function getContractListPrice(Product $product, User $user): string
    {
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

    private function getPriceListPrices(Product $product): array
    {
        $productPriceLists = $product->getProductPriceLists()->toArray();
        $productPriceListPrices = [];

        foreach ($productPriceLists as $list) {
            // $productPriceListPrices[$list->getPriceList()->getName()] = $list->getPrice();
            $productPriceListPrices[$list->getPriceList()->getId()] = $list->getPrice();
        }

        return $productPriceListPrices;
    }

    private function getContractListPrices(Product $product): array
    {
        $contractLists = $product->getContractLists()->toArray();
        $contractListPrices = [];

        foreach ($contractLists as $list) {
            // $contractListPrices[$list->getUser()->getName() . ' ' . $list->getUser()->getSurname()] = $list->getPrice();
            $contractListPrices[$list->getUser()->getId()] = $list->getPrice();
        }

        return $contractListPrices;
    }
}