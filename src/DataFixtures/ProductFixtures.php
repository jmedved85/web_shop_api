<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 50; $i++) {
            $product = new Product();

            $product
                ->setName("Product {$i}")
                ->setDescription("Description for Product {$i}")
                ->setSKU("SKU{$i}")
                ->setNetPrice(round(mt_rand(100, 1000) / 10.0, 2))
                ->setPublished($i <= 40)
            ;

            $manager->persist($product);
        }

        $manager->flush();
    }
}