<?php

namespace Celtic34fr\ContactGestion\DataFixtures;

use Bolt\Entity\User;
use Celtic34fr\ContactGestion\Entity\Category;
use Celtic34fr\ContactGestion\Entity\Contact;
use Celtic34fr\ContactGestion\Entity\Response;
use Celtic34fr\ContactGestion\Service\ManageTntIndexes;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
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

    private function createResponses(User $operateur, Contact $contact, ObjectManager $manager)
    {
        var_dump($contact->getId());

        $response = new Response();
        $response->setReponse(loremizer::getParagraph(3));
        $response->setOperateur($operateur);
        $response->setContact($contact);

        $maxCat = mt_rand(0, 5);
        /* ajout de 0 à 5 catégories */
        for ($j = 0; $j < $maxCat; ++$j) {
            $category = $manager->getRepository(Category::class)->findOneBy(['category' => 'catégory ' . strval(mt_rand(0, 9))]);
            $response->addCategory($category);
        }

        $manager->persist($response);
        $manager->flush();
    }
}
