<?php

namespace Celtic34fr\ContactGestion;

use Celtic34fr\ContactCore\Doctrine\ConnectionConfig;
use Celtic34fr\ContactCore\IndexGenerator;
use Celtic34fr\ContactCore\Service\ExtensionConfig;
use Celtic34fr\ContactGestion\Entity\Contacts;
use Celtic34fr\ContactGestion\Entity\Responses;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

class ManageTntIndexes
{
    private string $queryContacts;
    private string $queryResponses;
    private string $indexesContacts;
    private string $indexesResponses;

    public function __construct(private IndexGenerator $idxGenerator, private ExtensionConfig $extensionConfig,
                                private ConnectionConfig $connectionConfig, private EntityManagerInterface $entityManager)
    {
        $table = $this->entityManager->getClassMetadata(Contacts::class)->getTableName();
        $dql = $this->entityManager->createQueryBuilder()
                ->select(['idx.id', 'idx.sujet', 'idx.demande'])
                ->from(Contacts::class, 'idx');
        $sql = $dql->getQuery()->getDQL();
        $this->queryContacts = str_replace(Contacts::class, $table, $sql);

        $table = $this->entityManager->getClassMetadata(Responses::class)->getTableName();
        $dql = $this->entityManager->createQueryBuilder()
                ->select(['idx.id', 'idx.reponse'])
                ->from(Contacts::class, 'idx');
        $sql = $dql->getQuery()->getDQL();
        $this->queryResponses = str_replace(Contacts::class, $table, $sql);

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