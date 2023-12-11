<?php

namespace App\DataFixtures;

use App\Entity\City;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserFixtures extends Fixture
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load(ObjectManager $manager)
    {
        $cities = $this->entityManager->getRepository(City::class)->findAll();

        for ($i = 1; $i <= 5; $i++) {
            $user = new User();
            $randomCity = $cities[array_rand($cities)];

            $user
                ->setName('User ' . $i)
                ->setSurname('Surname ' . $i)
                ->setEmail('user' . $i . '@net.com')
                ->setPhone('123456789' . $i)
                ->setAddress($i . 'st Street')
                ->setCity($randomCity)
            ;

            $manager->persist($user);

            $this->addReference('user_' . $i, $user);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            StateCityFixtures::class,
        ];
    }
}