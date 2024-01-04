<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\PriceList;
use App\Entity\Product;
use App\Entity\ProductPriceList;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class ProductPriceListFixtures extends Fixture implements DependentFixtureInterface
{
    use FixturesTrait;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load(ObjectManager $manager): void
    {
        // Truncate 'product_price_list' table to reset auto-increment ID values
        $this->truncateTable(ProductPriceList::class);

        $products = $this->entityManager->getRepository(Product::class)->findAll();
        $priceLists = $this->entityManager->getRepository(PriceList::class)->findAll();

        foreach ($products as $product) {
            $netPrice = $product->getNetPrice();

            foreach ($priceLists as $priceList) {
                // A random check to associate the product with a price list
                if (rand(0, 1) === 1) {
                    $productPriceList = new ProductPriceList();

                    $productPriceList
                        ->setProduct($product)
                        ->setPriceList($priceList)
                        ->setPrice($this->calculatePrice($netPrice, $priceList->isIncreasedPrice()))
                    ;

                    $manager->persist($productPriceList);
                }
            }
        }

        $manager->flush();
    }

    private function calculatePrice(string $netPrice, ?bool $isIncreasedPrice): string
    {
        if ($isIncreasedPrice) {
            // Hardcoded increase of 10%
            $differentPrice = floatval($netPrice) * 1.1;

            return strval($differentPrice);
        } else if ($isIncreasedPrice == false) {
           // Hardcoded decrease of 10%
           $differentPrice = floatval($netPrice) * 0.9;

           return strval($differentPrice);
        }

        // If no condition for a different price, use netPrice
        return $netPrice;
    }

    public function getDependencies()
    {
        return [
            CategoryFixtures::class,
            ProductFixtures::class,
            PriceListFixtures::class,
        ];
    }
}