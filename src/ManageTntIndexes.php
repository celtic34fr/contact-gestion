<?php

namespace Celtic34fr\ContactGestion;

use Celtic34fr\ContactCore\Doctrine\ConnectionConfig;
use Celtic34fr\ContactCore\IndexGenerator;
use Celtic34fr\ContactCore\Service\ExtensionConfig;
use Symfony\Component\Filesystem\Filesystem;

class ManageTntIndexes
{
    private string $prefix;

    public function __construct(private IndexGenerator $idxGenerator, private ExtensionConfig $extensionConfig,
                                private ConnectionConfig $connectionConfig)
    {
        $this->prefix = $this->extensionConfig->get('bolt/table_prefix');
    }

    public function generateContactsIDX()
    {
        $sql = "SELECT idx.id, idx.sujet, idx.demande FROM ".$this->prefix."_contacts idx ";
        $indexes = 'contacts';
        $this->idxGenerator->generate($sql, $indexes);
    }

    public function generateResponsesIDX()
    {
        $sql = "SELECT idx.id, idx.reponse FROM ".$this->prefix."_responses idx ";
        $indexes = 'responses';
        $this->idxGenerator->generate($sql, $indexes);
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
}