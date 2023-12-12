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

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load(ObjectManager $manager)
    {
        // Truncate 'price_list' table to reset auto-increment ID values
        $this->truncateTable(PriceList::class);

        for ($i = 1; $i <= 5; $i++) {
            $priceList = new PriceList();

            $priceList->setName("Price List $i");

            $manager->persist($priceList);

            $this->addReference("price_list_$i", $priceList);
        }

        $manager->flush();
    }
}