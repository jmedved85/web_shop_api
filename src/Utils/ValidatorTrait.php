<?php

declare(strict_types=1);

namespace App\Utils;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\Constraints as CustomAssert;
use Exception;

trait ValidatorTrait
{
    private function pageValidator(Request $request, ValidatorInterface $validator): ?array
    {
        $errors = [];

        $page = $request->query->get('page', 1);
        $pageSize = $request->query->get('pageSize', 10);

        if (!is_numeric($page) || $page == '0') {
            $violations = $validator->validate($page, [
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
            $violations = $validator->validate($pageSize, [
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

    private function idValidator(Request $request, ValidatorInterface $validator): ?array
    {
        $errors = [];

        $userId = $request->query->get('userId');
        $priceListId = $request->query->get('priceListId');

        if ((!is_numeric($userId) && $userId !== null) || $userId == '0') {
            $violations = $validator->validate($userId, [
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
            $violations = $validator->validate($priceListId, [
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

    private function urlParamValidator(string $value, ValidatorInterface $validator, string $key = ''): ?int
    {
        $errors = [];

        if (!is_numeric($value) || $value == '0') {
            $violations = $validator->validate($value, [
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

    private function productFilterValidator(Request $request, ValidatorInterface $validator): ?array
    {
        $errors = [];

        $sortBy = $request->query->get('sortBy', 'name'); // Default sort by name
        $sortOrder = $request->query->get('sortOrder', 'asc'); // Default sort order ASC
        $filterByName = $request->query->get('name');
        $filterByCategory = $request->query->get('category');
        $filterByMaxPrice = $request->query->get('maxPrice');
        $filterByMinPrice = $request->query->get('minPrice');

        /* 'sortOrder' validation */
        $violations = $validator->validate($sortOrder, [
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
            $violations = $validator->validate($filterByMaxPrice, [
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
            $violations = $validator->validate($filterByMinPrice, [
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