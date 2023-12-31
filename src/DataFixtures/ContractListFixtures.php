<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\ContractList;
use App\Entity\Product;
use App\Entity\ProductPriceList;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class ContractListFixtures extends Fixture implements DependentFixtureInterface
{
    use FixturesTrait;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load(ObjectManager $manager): void
    {
        // Truncate 'contract_list' table to reset auto-increment ID values
        $this->truncateTable(ContractList::class);

        $users = $this->entityManager->getRepository(User::class)->findAll();
        $products = $this->entityManager->getRepository(Product::class)->findAll();

        foreach ($users as $user) {
            foreach ($products as $product) {
                if ($product->isPublished()) {
                    $contractList = new ContractList();
    
                    $contractList
                        ->setUser($user)
                        ->setProduct($product)
                    ;
    
                    $agreedPrice = $this->getAgreedPriceForProduct($product);
    
                    if ($agreedPrice !== null) {
                        $contractList->setPrice($agreedPrice);
                        $manager->persist($contractList);
                    }
                }
            }
        }

        $manager->flush();
    }

    private function getAgreedPriceForProduct(Product $product): ?string
    {
        $productPriceList = $this->entityManager->getRepository(ProductPriceList::class)
            ->findBy(['product' => $product])
        ;

        if (!empty($productPriceList)) {
            shuffle($productPriceList);

            return $productPriceList[0]->getPrice();
        }

        return null;
    }

    public function getDependencies()
    {
        return [
            CategoryFixtures::class,
            ProductFixtures::class,
            StateCityFixtures::class,
            UserFixtures::class,
            PriceListFixtures::class,
            ProductPriceListFixtures::class,
        ];
    }
}