<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends AbstractController
{
    public function index(Request $request, ProductRepository $repository): JsonResponse
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
            $data['items'][] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription(),
                'SKU' => $product->getSKU(),
                'netPrice' => $product->getNetPrice(),
                'published' => $product->getPublished(),
            ];
        }

        return $this->json($data);
    }
}