<?php

namespace App\DataFixtures\ContactGestion;

use Bolt\Entity\User;
use Celtic34fr\ContactGestion\Entity\Contacts;
use Celtic34fr\ContactGestion\Entity\Responses;
use Celtic34fr\ContactGestion\Entity\Categories;
use Celtic34fr\ContactGestion\Service\ManageTntIndexes;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Loremizer\loremizer;

class ResponsesFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function __construct(private ManageTntIndexes $manageTntIdx)
    {
    }

    public function load(ObjectManager $manager)
    {
        $operateur = new User();
        $operateur->setDisplayName('Opérateur test');
        $operateur->setUsername('operateur');
        $operateur->setPassword('operateur');
        $operateur->setEmail('operateur@dot.net');
        $manager->persist($operateur);
        $manager->flush();

        $contacts = $manager->getRepository(Contacts::class)->findAll();
        foreach ($contacts as $contact) {
            if ((bool) mt_rand(0, 1)) {
                $this->createResponses($operateur, $contact, $manager);
            }
        }
    }

    public function getDependencies()
    {
        return [
            CategoriesFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['responses'];
    }

    private function createResponses(User $operateur, Contacts $contact, ObjectManager $manager)
    {
        var_dump($contact->getId());

        $response = new Responses();
        $response->setReponse(loremizer::getParagraph(3));
        $response->setOperateur($operateur);
        $response->setContact($contact);

        $maxCat = mt_rand(0, 5);
        /* ajout de 0 à 5 catégories */
        for ($j = 0; $j < $maxCat; ++$j) {
            $category = $manager->getRepository(Categories::class)->findOneBy(['category' => 'catégorie ' . strval(mt_rand(0, 9))]);
            $response->addCategory($category);
        }

        $manager->persist($response);
        $manager->flush();
    }
}
