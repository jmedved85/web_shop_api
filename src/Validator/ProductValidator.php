<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;
use Exception;

class ProductValidator
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function pageValidator(Request $request): ?array
    {
        $errors = [];

        $page = $request->query->get('page', 1);
        $pageSize = $request->query->get('pageSize', 10);

        if (!is_numeric($page) || $page == '0') {
            $violations = $this->validator->validate($page, [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'integer', 'message' => 'Value must be an integer and must be positive.']),
            ]);

            if (count($violations) > 0) {
                foreach ($violations as $violation) {
                    $errors['page'][] = $violation->getMessage();
                }
            }
        }

        if (!is_numeric($pageSize) || $pageSize == '0') {
            $violations = $this->validator->validate($pageSize, [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'integer', 'message' => 'Value must be an integer and must be positive.']),
            ]);

            if (count($violations) > 0) {
                foreach ($violations as $violation) {
                    $errors['pageSize'][] = $violation->getMessage();
                }
            }
        }

        if (!empty($errors)) {
            throw new Exception(json_encode(['errors' => $errors]), 400);
        }

        return [(int)$page, (int)$pageSize];
    }

    public function idValidator(Request $request): ?array
    {
        $errors = [];

        $userId = $request->query->get('userId');
        $priceListId = $request->query->get('priceListId');

        if ((!is_numeric($userId) && $userId !== null) || $userId == '0') {
            $violations = $this->validator->validate($userId, [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'integer', 'message' => 'Value must be an integer and must be positive.']),
            ]);

            if (count($violations) > 0) {
                foreach ($violations as $violation) {
                    $errors['userId'][] = $violation->getMessage();
                }
            }
        }

        if ((!is_numeric($priceListId) && $priceListId !== null) || $priceListId == '0') {
            $violations = $this->validator->validate($priceListId, [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'integer', 'message' => 'Value must be an integer and must be positive.']),
            ]);

            if (count($violations) > 0) {
                foreach ($violations as $violation) {
                    $errors['priceListId'][] = $violation->getMessage();
                }
            }
        }

        if (!empty($errors)) {
            throw new Exception(json_encode(['errors' => $errors]), 400);
        }

        return [$userId ? (int)$userId : null, $priceListId ? (int)$priceListId : null];
    }

    public function urlParamValidator(string $value, string $key = ''): ?int
    {
        $errors = [];

        if (!is_numeric($value) || $value == '0') {
            $violations = $this->validator->validate($value, [
                new Assert\NotBlank(),
                new Assert\Type(['type' => 'integer', 'message' => 'Value must be an integer and must be positive.']),
            ]);

            if (count($violations) > 0) {
                foreach ($violations as $violation) {
                    $errors[$key][] = $violation->getMessage();
                }
            }

            if (!empty($errors)) {
                throw new Exception(json_encode(['errors' => $errors]), 400);
            }
        }

        return $value ? (int)$value : null;
    }

    public function productFilterValidator(Request $request): ?array
    {
        $errors = [];

        $sortBy = $request->query->get('sortBy', 'name'); // Default sort by name
        $sortOrder = $request->query->get('sortOrder', 'asc'); // Default sort order ASC
        $filterByName = $request->query->get('name');
        $filterByCategory = $request->query->get('category');
        $filterByMaxPrice = $request->query->get('maxPrice');
        $filterByMinPrice = $request->query->get('minPrice');

        /* 'sortBy' validation */
        $violations = $this->validator->validate($sortBy, [
            new Assert\NotBlank(),
            new Assert\Choice(['choices' => ['name', 'netPrice', 'SKU', 'description', 'published'], 
                'message' => 'The value you selected is not a valid choice (valid choices are \'name\', \'netPrice\', \'SKU\', \'description\' and \'published\').']),
        ]);

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors['sortBy'][] = $violation->getMessage();
            }
        }

        /* 'sortOrder' validation */
        $violations = $this->validator->validate($sortOrder, [
            new Assert\NotBlank(),
            new Assert\Choice(['choices' => ['asc', 'desc'], 
                'message' => 'The value you selected is not a valid choice (valid choices are \'asc\' and \'desc\').']),
        ]);

        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $errors['sortOrder'][] = $violation->getMessage();
            }
        }

        /* 'maxPrice' & 'minPrice' validations */
        if (!is_numeric($filterByMaxPrice)) {
            $violations = $this->validator->validate($filterByMaxPrice, [
                new Assert\NotBlank(),
                new Assert\PositiveOrZero(),
                new CustomAssert\Currency()
            ]);

            if (count($violations) > 0) {
                foreach ($violations as $violation) {
                    $errors['maxPrice'][] = $violation->getMessage();
                }
            }
        }

        if (!is_numeric($filterByMinPrice)) {
            $violations = $this->validator->validate($filterByMinPrice, [
                new Assert\NotBlank(),
                new Assert\PositiveOrZero(),
                new CustomAssert\Currency()
            ]);

            if (count($violations) > 0) {
                foreach ($violations as $violation) {
                    $errors['minPrice'][] = $violation->getMessage();
                }
            }
        }

        if (!empty($errors)) {
            throw new Exception(json_encode(['errors' => $errors]), 400);
        }

        return [$sortBy, $sortOrder, $filterByName, $filterByCategory, $filterByMaxPrice, $filterByMinPrice];
    }
}