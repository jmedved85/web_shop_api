<?php

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
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load(ObjectManager $manager)
    {
        $products = $this->entityManager->getRepository(Product::class)->findAll();
        $categories = $this->entityManager->getRepository(Category::class)->findAll();

        foreach ($products as $product) {
            // Ensure that each product has at least one category (3 categories max for each product)
            $numberOfCategories = max(1, mt_rand(1, min(3, count($categories))));
    
            shuffle($categories);
    
            // Take the first $numberOfCategories categories
            $selectedCategories = array_slice($categories, 0, $numberOfCategories);
    
            foreach ($selectedCategories as $category) {
                $productCategory = new ProductCategory();
                $productCategory->setProduct($product);
                $productCategory->setCategory($category);
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