<?php

namespace Celtic34fr\ContactGestion;

use Celtic34fr\ContactCore\Doctrine\ConnectionConfig;
use Celtic34fr\ContactCore\IndexGenerator;
use Celtic34fr\ContactCore\Service\ExtensionConfig;
use Celtic34fr\ContactGestion\Entity\Contacts;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class ManageTntIndexes
{
    private string $prefix;
    private string $queryContacts;
    private string $queryResponses;
    private string $indexesContacts;
    private string $indexesResponses;

    public function __construct(private IndexGenerator $idxGenerator, private ExtensionConfig $extensionConfig,
                                private ConnectionConfig $connectionConfig, private EntityManagerInterface $entityManager)
    {
        $dql = $this->entityManager->createQueryBuilder()
                ->select(['idx.id', 'idx.sujet', 'idx.demande'])
                ->from(Contacts::class, 'idx');
        $sql = $dql->getQuery()->getSQL();


        $this->prefix = $this->extensionConfig->get('celtic34fr-contactcore/prefix');
        $this->queryContacts = "SELECT idx.id, idx.sujet, idx.demande FROM ".$this->prefix."_contacts idx ";
        $this->queryResponses = "SELECT idx.id, idx.reponse FROM ".$this->prefix."_responses idx ";
        $this->indexesContacts = 'contacts';
        $this->indexesResponses = 'responses';
    }

    public function generateContactsIDX()
    {
        $this->idxGenerator->generate($this->queryContacts, $this->indexesContacts);
    }

    public function generateResponsesIDX()
    {
        $this->idxGenerator->generate($this->queryResponses, $this->indexesResponses);
    }

    public function generate()
    {
        $this->generateContactsIDX();
        $this->generateResponsesIDX();
    }

    public function refreshContactsIDX()
    {
        $config = $this->connectionConfig->getConfig();
        $fileName = $config['storage'].'/contacts.index';
        $filesystem = new Filesystem();
        if (!$filesystem->exists($fileName)) {
            $filesystem->remove($fileName);
        }
        $this->generateContactsIDX();
    }

    public function refreshResponsesIDX()
    {
        $config = $this->connectionConfig->getConfig();
        $fileName = $config['storage'].'/responses.index';
        $filesystem = new Filesystem();
        if (!$filesystem->exists($fileName)) {
            $filesystem->remove($fileName);
        }
        $this->generateResponsesIDX();
    }

    public function refresh()
    {
        $this->refreshContactsIDX();
        $this->refreshResponsesIDX();
    }

    public function updateContactsIDX(array $srcArray, string $operation) {
        $this->idxGenerator->updateByArray($this->indexesContacts, $srcArray,  $operation);
    }

    public function updateResponsesIDX(array $srcArray, string $operation) {
        $this->idxGenerator->updateByArray($this->indexesResponses, $srcArray, $operation);
    }
}