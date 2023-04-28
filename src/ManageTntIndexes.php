<?php

namespace Celtic34fr\ContactGestion;

use TeamTNT\TNTSearch\TNTSearch;
use Doctrine\ORM\EntityManagerInterface;
use Celtic34fr\ContactCore\IndexGenerator;
use Symfony\Component\Filesystem\Filesystem;
use Celtic34fr\ContactGestion\Entity\Contacts;
use Celtic34fr\ContactGestion\Entity\Responses;
use Celtic34fr\ContactCore\Service\ExtensionConfig;
use Celtic34fr\ContactCore\Doctrine\ConnectionConfig;

class ManageTntIndexes
{
    private string $queryContacts;
    private string $queryResponses;
    public string $indexesContacts;
    public string $indexesResponses;
    public $fuzzy_prefix_length  = 2;
    public $fuzzy_max_expansions = 50;
    public $fuzzy_distance       = 2;
    
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

        $this->indexesContacts = 'contacts.index';
        $this->indexesResponses = 'responses.index';
    }

    public function getTntConfig() {
        return $this->connectionConfig->getConfig();
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

    public function searchContact(string $toSearch, int $maxResults = 20)
    {
        $tnt = new TNTSearch;
        $configuration = $this->getTntConfig();
        $tnt->loadConfig($configuration);
        $results = [];

        // recherche dans chaque index 
        $tnt->selectIndex($this->indexesContacts);
        $this->fuzzy_prefix_length = $this->extensionConfig->get('celtic34fr-contactcore/fuzzy/prefix_length');
        $this->fuzzy_max_expansions = $this->extensionConfig->get('celtic34fr-contactcore/fuzzy/max_expansions');
        $this->fuzzy_distance = $this->extensionConfig->get('celtic34fr-contactcore/fuzzy/distance');
        $tnt->fuzziness = $this->extensionConfig->get('celtic34fr-contactcore/fuzzy/enabled');
        if ($maxResults > 0) {
            $tntResult = $tnt->search($toSearch, $maxResults);
        } else { // limitation à 100 résultats par défaut
            $tntResult = $tnt->search($toSearch);
        }
        if ($tntResult) { // la recherche à produit des résultat
            foreach ($tntResult['ids'] as $idResult) {
                $record = $this->entityManager->getRepository(Contacts::class)->find($idResult);
                $results[$idResult] = $this->formatQR($record, 'contacts', $tntResult['docScores'][$idResult]);
            }
        }
        return $results;
    }

    public function searchResponse(string $toSearch, int $maxResults = 20)
    {
        $tnt = new TNTSearch;
        $configuration = $this->getTntConfig();
        $tnt->loadConfig($configuration);
        $results = [];

        // recherche dans chaque index 
        $tnt->selectIndex($this->indexesResponses);
        $tntResult = $tnt->search($toSearch, $maxResults);
        if ($tntResult) { // la recherche à produit des résultat
            foreach ($tntResult['ids'] as $idResult) {
                $record = $this->entityManager->getRepository(Responses::class)->find($idResult);
                $results[$idResult] = $this->formatQR($record, 'contacts', $tnt['docScores'][$idResult]);
            }
        }
        return $results;
    }

    public function search (string $toSearch, int $maxResults = 20)
    {
        $resultsC = $this->searchContact($toSearch, $maxResults) ?? [];
        $resultsR = $this->searchResponse($toSearch, $maxResults) ?? [];
        $results = $this->array_unique(array_merge($resultsC, $resultsR));
        return $results;
    }


    private function formatQR($object, string $entity, $score) {
        /* formatage des résultats de recherche pour obtebir une structure unique quelque soit l'objet à traiter */
        switch($entity) {
            case 'contacts':
                /** @var Contacts $object */
                $contact = $object;
                $response = $object->getReponse();
                break;
            case 'responses':
                /** @var Responses $object */
                $response = $object;
                $contact = $object->getContact();
                break;
        }
        return [
            'id' => $contact->getId(),
            'sujet' => $contact->getSujet(),
            'demande' => $contact->getDemande(),
            'createdAt' => $contact->getCreatedAt() ? $contact->getCreatedAt()->format('d/m/Y') : '',
            'treatedAt' => $contact->getTreatedAt() ? $contact->getTreatedAt()->format('d/m/Y') : '',
            'reponse' => $response ? $response->getReponse() : '',
            'sendAt' => $response && $response->getSendAt() ? $response->getSendAt()->format('d/m/Y') : '',
            'score' => $score,
        ];
    }

    private function array_unique(array $array, int $maxResults = 20) {
        $rslt = [];
        foreach($array as $id => $data) {
            if (!array_key_exists($id, $rslt)) {
                if ($maxResults && sizeof($rslt) === $maxResults) {
                    break;
                }
                $rslt[$id] = $data;
            }
        }
        return $rslt;
    }
}