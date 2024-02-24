<?php

namespace Celtic34fr\ContactGestion\DataFixtures\ContactGestion;

use Celtic34fr\ContactCore\Entity\Clientele;
use Celtic34fr\ContactCore\Entity\CliInfos;
use Celtic34fr\ContactGestion\DataFixtures\ContactCore\ClientelesFixtures;
use Celtic34fr\ContactGestion\Entity\Contact;
use Celtic34fr\ContactGestion\Service\ManageTntIndexes;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Loremizer\loremizer;

class ContactsFixtures extends Fixture implements FixtureGroupInterface
{
    public const CONTACT_REFERENCE = 'contact';

    public function __construct(private ManageTntIndexes $manageTntIdx)
    {
    }

    public function load(ObjectManager $manager)
    {
        /** Clientele $client */
        $client = $manager->getRepository(Clientele::class)->findAll()[0];
        $i = 0;

        foreach ($client->getCliInfos() as $cliinfos) {
            $this->createContact($i, $cliinfos, $manager);
            $i++;
        }
        $this->manageTntIdx->generateContactsIDX();
    }

    public static function getGroups(): array
    {
        return ['contacts', 'categories', 'responses'];
    }

    public function getDependencies()
    {
        return [
            ClientelesFixtures::class,
        ];
    }

    private function createCliinfo(int $noCliinfo, Clientele $client, ObjectManager $manager): CliInfos
    {
        $cliinfos = new CliInfos();
        $cliinfos->setNom('TEST ' . $noCliinfo);
        $cliinfos->setPrenom('Ptest' . $noCliinfo);
        $cliinfos->setTelephone('0102030405');
        $cliinfos->setClient($client);
        $client->addCliInfos($cliinfos);
        $manager->persist($cliinfos);
        $manager->flush();

        return $cliinfos;
    }

    private function createContact(int $noContact, CliInfos $client, ObjectManager $manager): void
    {
        $contact = new Contact();
        $contact->setSujet($noContact . ' ' . loremizer::getTitle());
        $contact->setDemande(loremizer::getParagraph(3));
        $contact->setClient($client);
        $contact->setContactMe((bool) mt_rand(0, 1));
        $manager->persist($contact);
        $manager->flush();
    }
}
