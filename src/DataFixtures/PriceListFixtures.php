<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\PriceList;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class PriceListFixtures extends Fixture
{
    use FixturesTrait;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load(ObjectManager $manager): void
    {
        // Truncate 'price_list' table to reset auto-increment ID values
        $this->truncateTable(PriceList::class);

        $increaseOptions = [true, false, null];

        for ($i = 1; $i <= 5; $i++) {
            $priceList = new PriceList();

            // Get a random index from the $increaseOptions array
            $randomIndex = array_rand($increaseOptions);

            // Assign the value at the random index to increasedPrice
            $increasedPriceValue = $increaseOptions[$randomIndex];

            $priceList->setName("Price List $i");

            if ($increasedPriceValue === null) {
                $priceList->setIncreasedPrice(null);
            } else {
                $priceList->setIncreasedPrice((bool) $increasedPriceValue);
            }

            $manager->persist($priceList);

            $this->addReference("price_list_$i", $priceList);
        }

        $manager->flush();
    }
}