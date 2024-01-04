<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\MainCategory;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    use FixturesTrait;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load(ObjectManager $manager): void
    {
        // Truncate 'category' & 'main_category' tables to reset auto-increment ID values
        $this->truncateTable(Category::class);
        $this->truncateTable(MainCategory::class);

        for ($i = 1; $i <= 3; $i++) {
            $mainCategory = new MainCategory();

            $mainCategory
                ->setName("Main Category $i")
                ->setDescription("Description for Main Category $i")
            ;

            $manager->persist($mainCategory);

            $this->addReference("main_category_$i", $mainCategory);

            for ($j = 1; $j <= 5; $j++) {
                $category = new Category();

                $category
                    ->setName("Category $j for Main Category $i")
                    ->setDescription("Description for Category $j")
                    ->setMainCategory($mainCategory)
                ;

                $manager->persist($category);
            }
        }

        $manager->flush();
    }
}
