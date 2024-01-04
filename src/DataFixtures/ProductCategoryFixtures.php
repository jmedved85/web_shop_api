<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\ProductCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class ProductCategoryFixtures extends Fixture implements DependentFixtureInterface
{
    use FixturesTrait;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load(ObjectManager $manager): void
    {
        // Truncate 'product_category' table to reset auto-increment ID values
        $this->truncateTable(ProductCategory::class);

        $products = $this->entityManager->getRepository(Product::class)->findAll();
        $categories = $this->entityManager->getRepository(Category::class)->findAll();

        foreach ($products as $product) {
            // Ensure that each product has at least one category (2 categories max for each product)
            $numberOfCategories = max(1, mt_rand(1, min(2, count($categories))));
    
            shuffle($categories);
    
            // Take the first $numberOfCategories categories
            $selectedCategories = array_slice($categories, 0, $numberOfCategories);
    
            foreach ($selectedCategories as $category) {
                $productCategory = new ProductCategory();
                $productCategory
                    ->setProduct($product)
                    ->setCategory($category)
                ;
                
                $manager->persist($productCategory);
            }
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CategoryFixtures::class,
            ProductFixtures::class,
        ];
    }
}