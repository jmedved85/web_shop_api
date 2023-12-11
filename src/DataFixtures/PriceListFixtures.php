<?php

namespace App\DataFixtures;

use App\Entity\PriceList;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PriceListFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i <= 5; $i++) {
            $priceList = new PriceList();

            $priceList->setName("Price List $i");

            $manager->persist($priceList);

            $this->addReference("price_list_$i", $priceList);
        }

        $manager->flush();
    }
}