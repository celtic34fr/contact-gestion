<?php

namespace App\DataFixtures\ContactGestion;

use Celtic34fr\ContactGestion\Entity\Categories;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CategoriesFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 10; ++$i) {
            $this->createCategorie($i, $manager);
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

    private function createCategorie(int $idx, ObjectManager $manager)
    {
        $category = new Categories();
        $category->setCategory("catégorie $idx");
        $manager->persist($category);
        $manager->flush();
    }
}
