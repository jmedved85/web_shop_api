<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\State;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class StateCityFixtures extends Fixture
{
    use FixturesTrait;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load(ObjectManager $manager): void
    {
        // Truncate 'city' and 'state' tables to reset auto-increment ID values
        $this->truncateTable(City::class);
        $this->truncateTable(State::class);

        for ($i = 1; $i <= 3; $i++) {
            $state = new State();

            $state
                ->setName("State $i")
            ;

            $manager->persist($state);

            $this->addReference("state_$i", $state);

            for ($j = 1; $j <= 5; $j++) {
                $city = new City();

                $city
                    ->setName("City $j for State $i")
                    ->setState($state)
                ;

                $manager->persist($city);
            }
        }

        $manager->flush();
    }
}
