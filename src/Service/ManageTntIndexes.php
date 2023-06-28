<?php

namespace Celtic34fr\ContactGestion\Service;

use TeamTNT\TNTSearch\TNTSearch;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Celtic34fr\ContactGestion\Entity\Contact;
use Celtic34fr\ContactGestion\Entity\Response;
use Celtic34fr\ContactCore\Service\IndexGenerator;
use Celtic34fr\ContactCore\Service\ExtensionConfig;
use Celtic34fr\ContactCore\Doctrine\ConnectionConfig;

/** classe de gestion des index générés par l'outil TNTSearch */
class ManageTntIndexes
{
    private string $queryContacts;
    private string $queryResponses;
    public string $indexesContacts;
    public string $indexesResponses;
    public $fuzzy_prefix_length = 2;
    public $fuzzy_max_expansions = 50;
    public $fuzzy_distance = 2;

    public function __construct(private IndexGenerator $idxGenerator, private ExtensionConfig $extensionConfig,
        private ConnectionConfig $connectionConfig, private EntityManagerInterface $entityManager) 
    {
        $table = $this->entityManager->getClassMetadata(Contact::class)->getTableName();
        $dql = $this->entityManager->createQueryBuilder()
                ->select(['idx.id', 'idx.sujet', 'idx.demande'])->from(Contact::class, 'idx');
        $sql = $dql->getQuery()->getDQL();
        $this->queryContacts = str_replace(Contact::class, $table, $sql);

        $table = $this->entityManager->getClassMetadata(Response::class)->getTableName();
        $dql = $this->entityManager->createQueryBuilder()
                ->select(['idx.id', 'idx.reponse'])
                ->from(Contact::class, 'idx');
        $sql = $dql->getQuery()->getDQL();
        $this->queryResponses = str_replace(Contact::class, $table, $sql);

        $this->indexesContacts = 'contacts';
        $this->indexesResponses = 'responses';

        /** contrôle existance répoertoire stoage des index TNTSearch et création au besoin */
        $filesystem = new Filesystem();
        $config = $this->getTntConfig();
        if (!$filesystem->exists($config['storage'])) {
            $filesystem->mkdir($config['storage']);
            $filesystem->chgrp($config['storage'], 'www-data', true);
            $filesystem->chmod($config['storage'], 0777);
            /** gération des index TNTSearch */
            $this->generate();
        }
    }

    /** récupération de la configuation pour TNTSearch */
    public function getTntConfig()
    {
        return $this->connectionConfig->getConfig();
    }

    /** génération de l'index de recherche sur la table Contacts avec TNTSearch */
    public function generateContactsIDX()
    {
        $this->idxGenerator->generate($this->queryContacts, $this->indexesContacts);
    }

    /** génération de l'index de recherche sur la table Responses avec TNTSearch */
    public function generateResponsesIDX()
    {
        $this->idxGenerator->generate($this->queryResponses, $this->indexesResponses);
    }

    /** génération des index de recherche avec TNTSearch */
    public function generate()
    {
        $this->generateContactsIDX();
        $this->generateResponsesIDX();
    }

    /** mise à jour de l'index de recherche sur la table Contacts avec TNTSearch */
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

    /** mise à jour de l'index de recherche sur la table Responses avec TNTSearch */
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

    /** mise à jour des index de recherche avec TNTSearch */
    public function refresh()
    {
        $this->refreshContactsIDX();
        $this->refreshResponsesIDX();
    }

    /** mise à jour de l'index de recherche sur la table Contacts avec TNTSearch avec tableu d'information */
    public function updateContactsIDX(array $srcArray, string $operation)
    {
        $this->idxGenerator->updateByArray($this->indexesContacts, $srcArray, $operation);
    }

    /** mise à jour de l'index de recherche sur la table Responses avec TNTSearch avec tableu d'information */
    public function updateResponsesIDX(array $srcArray, string $operation)
    {
        $this->idxGenerator->updateByArray($this->indexesResponses, $srcArray, $operation);
    }

    /** méthode de recherche dans l'index de recherche sur la table Contacts avec TNTSearch */
    public function searchContact(string $toSearch, int $maxResults = 0)
    {
        $tnt = new TNTSearch();
        $configuration = $this->getTntConfig();
        $tnt->loadConfig($configuration);
        $results = [];

        // recherche dans chaque index
        $tnt->selectIndex($this->indexesContacts.'.index');
        $this->fuzzy_prefix_length = $this->extensionConfig->get('celtic34fr-contactcore/fuzzy/prefix_length');
        $this->fuzzy_max_expansions = $this->extensionConfig->get('celtic34fr-contactcore/fuzzy/max_expansions');
        $this->fuzzy_distance = $this->extensionConfig->get('celtic34fr-contactcore/fuzzy/distance');
        $tnt->fuzziness = $this->extensionConfig->get('celtic34fr-contactcore/fuzzy/enabled') ?? false;
        if ($maxResults > 0) {
            $tntResult = $tnt->search($toSearch, $maxResults);
        } else { // limitation à 100 résultats par défaut
            $tntResult = $tnt->search($toSearch);
        }

        if (!empty($tntResult)) { // la recherche à produit des résultat
            if (sizeof($tntResult['ids'])) {
                foreach ($tntResult['ids'] as $idResult) {
                    $record = $this->entityManager->getRepository(Contact::class)->find((int) $idResult);
                    $result = $this->formatQR($record, 'contacts', $tntResult['docScores'][$idResult]);
                    if (!empty($result)) {
                        $results[$result['id']] = $result;
                    }
                }
            } else {
                if ((int) $tntResult['hits'] === 1 && sizeof($tntResult['docScores']) === 1)
                {
                    $idContact = (int) array_keys($tntResult['docScores'])[0];
                    $record = $this->entityManager->getRepository(Contact::class)->find($idContact);
                    $result = $this->formatQR($record, 'contacts', $tntResult['docScores'][$idContact]);
                    $results[$result['id']] = $result;
                }
            }
        }
        return $results;
    }

    /** méthode de recherche dans l'index de recherche sur la table Responses avec TNTSearch */
    public function searchResponse(string $toSearch, int $maxResults = 0)
    {
        $tnt = new TNTSearch();
        $configuration = $this->getTntConfig();
        $tnt->loadConfig($configuration);
        $results = [];

        // recherche dans chaque index
        $tnt->selectIndex($this->indexesResponses.'.index');
        $this->fuzzy_prefix_length = $this->extensionConfig->get('celtic34fr-contactcore/fuzzy/prefix_length');
        $this->fuzzy_max_expansions = $this->extensionConfig->get('celtic34fr-contactcore/fuzzy/max_expansions');
        $this->fuzzy_distance = $this->extensionConfig->get('celtic34fr-contactcore/fuzzy/distance');
        $tnt->fuzziness = $this->extensionConfig->get('celtic34fr-contactcore/fuzzy/enabled') ?? false;
        $tntResult = $tnt->search($toSearch, $maxResults);

        if (!empty($tntResult)) { // la recherche à produit des résultat
            if (sizeof($tntResult['ids'])) {
                foreach ($tntResult['ids'] as $idResult) {
                    $record = $this->entityManager->getRepository(Response::class)->find($idResult);
                    $result = $this->formatQR($record, 'responses', $tnt['docScores'][$idResult]);
                    if (!empty($result)) {
                        $results[$result['id']] = $result;
                    }
                }
            } else {
                if ((int) $tntResult['hits'] === 1 && sizeof($tntResult['docScores']) === 1)
                {
                    $idResponse = (int) array_keys($tntResult['docScores'])[0];
                    $record = $this->entityManager->getRepository(Response::class)->find($idResponse);
                    $result = $this->formatQR($record, 'responses', $tntResult['docScores'][$idResponse]);
                    $results[$result['id']] = $result;
                }
            }
        }
        return $results;
    }

    /** méthode de recherche dans les index de recherche avec TNTSearch */
    public function search(string $toSearch, int $maxResults = 0)
    {
        $resultsC = $this->searchContact($toSearch, $maxResults) ?? [];
        $resultsR = $this->searchResponse($toSearch, $maxResults) ?? [];
        $results = array_replace_recursive($resultsC, $resultsR);
        return $results;
    }

    /** méthodes privées */

    private function formatQR($object, string $entity, float $score)
    {
        $contact = null;
        $response = null;
        /* formatage des résultats de recherche pour obtebir une structure unique quelque soit l'objet à traiter */
        switch ($entity) {
            case 'contacts':
                $contact = $object;
                $response = $object ? $object->getReponse() : null;
                break;
            case 'responses':
                $response = $object;
                $contact = $object ? $object->getContact() : null;
                break;
        }
        $record = [];
        /** @var Contact $contact */
        /** @var Response $response */
        if (!empty($contact)) {
            return [
                'id' => $contact->getId(),
                'fullname' => $contact->getClient()->getFullName(),
                'sujet' => $contact->getSujet(),
                'demande' => $contact->getDemande(),
                'createdAt' => $contact->getCreatedAt() ? $contact->getCreatedAt()->format('d/m/Y') : '',
                'treatedAt' => $contact->getTreatedAt() ? $contact->getTreatedAt()->format('d/m/Y') : '',
                'idResponses' => $response ? $response->getId() : '',
                'respondBy' => $response ? $response->getOperateur()->getDisplayName() : '',
                'reponse' => $response ? $response->getReponse() : '',
                'sendAt' => $response && $response->getSendAt() ? $response->getSendAt()->format('d/m/Y') : '',
                'closedAt' => $response && $response->getClosedAt() ? $response->getClosedAt()->format('d/m/Y') : '',
                'score' => $score,
            ];
        }
        return [];
    }

    private function array_unique(array $array, int $maxResults = 0)
    {
        $rslt = [];
        foreach ($array as $id => $data) {
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
