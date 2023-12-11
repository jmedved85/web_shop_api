<?php

namespace App\DataFixtures;

use App\Entity\MainCategory;
use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
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
