<?php

namespace App\DataFixtures\ContactGestion;

use Celtic34fr\ContactGestion\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CategoriesFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 10; ++$i) {
            $this->createCategory($i, $manager);
        }
    }

    public static function getGroups(): array
    {
        return ['categories', 'responses'];
    }

    public function getDependencies()
    {
        return [
            ContactsFixtures::class,
        ];
    }

    private function createCategory(int $idx, ObjectManager $manager)
    {
        $category = new Category();
        $category->setCategory("catégory $idx");
        $manager->persist($category);
        $manager->flush();
    }
}
