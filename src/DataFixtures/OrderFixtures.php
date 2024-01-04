<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\OrderProduct;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class OrderFixtures extends Fixture
{
    use FixturesTrait;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load(ObjectManager $manager): void
    {
        // Truncate 'order_product' and 'order' tables to reset auto-increment ID values
        $this->truncateTable(OrderProduct::class);
        $this->truncateTable(Order::class);

        $manager->flush();
    }
}
