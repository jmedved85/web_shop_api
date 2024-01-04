<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    use FixturesTrait;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load(ObjectManager $manager): void
    {
        // Truncate 'product' table to reset auto-increment ID values
        $this->truncateTable(Product::class);

        for ($i = 1; $i <= 50; $i++) {
            $product = new Product();

            $product
                ->setName("Product {$i}")
                ->setDescription("Description for Product {$i}")
                ->setSKU("SKU{$i}")
                ->setNetPrice(strval(round(mt_rand(100, 1000) / 10.0, 2)))
                ->setPublished($i <= 40)
            ;

            $manager->persist($product);
        }

        $manager->flush();
    }
}