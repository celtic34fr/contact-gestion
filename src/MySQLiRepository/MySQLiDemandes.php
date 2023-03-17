<?php

namespace Celtic34fr\ContactGestion\MySQLiRepository;

use Celtic34fr\ContactCore\MySQLiRepository\MySQLiCliInfos;
use Celtic34fr\ContactCore\Trait\MySQLiPaginateTrait;
use Celtic34fr\ContactCore\Trait\MySQLiTrait;
use Celtic34fr\ContactGestion\MySQLiEntity\MSIDemandes;
use DateTimeImmutable;
use Exception;

/**
 * Class MySQLiCrmDemandes : accès via mysqli à l'entité CrmDemandes
 *
 * find(int $table_id)                                          : recherche directe d'un enregistrement par champ ID
 *
 * findRequestAll(int $currentPage = 1, int $limit = 10)        : recherche des demandes à traiter (avec pagination)
 * countRequestAll()                                            : compte du nombre de demandes à traiter
 * findLast2WeeksDemands(int $currentPage = 1, int $limit = 10) :
 *                                                        demandes crées sur les 2 dernières semaines (avec pagination)
 * countLast2WeeksDemands()                                     :
 *                                                     compte du nombre de demandes créées sur les 2 dernières semaines
 * findDemandGoToEnd(int $currentPage = 1, int $limit = 10)     : demandes dont le délai expire entre jour et jour + 1
 * countDemandGoToEnd()                                         :
 *                                             compte du nombre de demandes dont le délai expire entre jour et jour + 1
 * findDemandOutOfTime(int $currentPage = 1, int $limit = 10)   : demandes hors délai (avec pagination)
 * countDemandOutOfTime()                                       : compte du nombre de demandes hors délai
 */
class MySQLiDemandes
{
    use MySQLiTrait {
        MySQLiTrait::__construct as mysqli_construct;
    }
    use MySQLiPaginateTrait;

    private string $dbPrefix;
    private string $dbName;
    private string $className;
    protected array $structure;

    public function __construct()
    {
        $this->mysqli_construct();
        $this->dbPrefix = $_ENV['DB_PREFIX'] ?? "";
        $this->dbName = $this->dbPrefix . "demandes";
        $this->className = MSIDemandes::class;
        $this->structure = $this->getTableColumns();
    }

    /**
     * @param int $currentPage
     * @param int $limit
     * @return array
     * @throws Exception
     */
    public function findRequestAll(int $currentPage = 1, int $limit = 10): array
    {
        $sql = "SELECT * FROM " . $this->dbName . " bcd ";
        $sql .= "WHERE bcd.closed_at IS NULL ORDER BY bcd.created_at DESC";
        $stmt = $this->conn->query($sql);
        $rows = [];
        $client = new MySQLiCliInfos();
        if (mysqli_num_rows($stmt) > 0) {
            /** @var MSIDemandes $row */
            while($row = mysqli_fetch_object($stmt, $this->className)){
//                $row = $this->hydrate($row);
                $row->setClient($client->find((int) $row->getClientId()));
                $rows[$row->getId()] = $row;
            }
        }

        if ($currentPage === 0 && $limit === 0) {
            return [
                'datas' => $rows,
                'pages' => 0,
                'page' => 0,
            ];
        }
        return $this->paginateMSI($rows, $currentPage, $limit);
    }

    /**
     * @return int
     */
    public function countRequestAll(): int
    {
        return sizeof($this->findRequestAll(0, 0)['datas']) ?? 0;
    }

    /**
     * @param int $currentPage
     * @param int $limit
     * @return array
     */
    public function findLast2WeeksDemands(int $currentPage = 1, int $limit = 10): array
    {
        $date = (new DateTimeImmutable('now'))->modify("-2 weeks");
        $sql = "SELECT * FROM " . $this->dbName . " bcd ";
        $sql .= "WHERE bcd.created_at > '" . $date->format('Y-m-d') . "'";
        $sql .= " ORDER BY bcd.created_at DESC";
        $stmt = $this->conn->query($sql);
        $rows = [];
        $client = new MySQLiCliInfos();
        if (mysqli_num_rows($stmt) > 0) {
            /** @var MSIDemandes $row */
            while($row = mysqli_fetch_object($stmt, $this->className)){
//                $row = $this->hydrate($row);
                $row->setClient($client->find((int) $row->getClientId()));
                $rows[$row->getId()] = $row;
            }
        }

        if ($currentPage === 0 && $limit === 0) {
            return [
                'datas' => $rows,
                'pages' => 0,
                'page' => 0,
            ];
        }
        return $this->paginateMSI($rows, $currentPage, $limit);
    }

    /**
     * @return int|void
     */
    public function countLast2WeeksDemands()
    {
        return sizeof($this->findLast2WeeksDemands(0, 0)['datas']) ?? 0;
    }

    /**
     * @param int $currentPage
     * @param int $limit
     * @return array
     */
    public function findDemandGoToEnd(int $currentPage = 1, int $limit = 10): array
    {
        $dateDeb = (new DateTimeImmutable('now'))->modify("-2 weeks");
        $dateFin = $dateDeb->modify('+1 day');
        $sql = "SELECT * FROM " . $this->dbName . " bcd ";
        $sql .= "WHERE bcd.created_at >= '" . $dateDeb->format('Y-m-d') . "' ";
        $sql .= "AND bcd.created_at <= '" . $dateFin->format('Y-m-d') . "' ";
        $sql .= " ORDER BY bcd.created_at DESC";
        $stmt = $this->conn->query($sql);
        $rows = [];
        $client = new MySQLiCliInfos();
        if (mysqli_num_rows($stmt) > 0) {
            /** @var MSIDemandes $row */
            while($row = mysqli_fetch_object($stmt, $this->className)){
//                $row = $this->hydrate($row);
                $row->setClient($client->find((int) $row->getClientId()));
                $rows[$row->getId()] = $row;
            }
        }

        if ($currentPage === 0 && $limit === 0) {
            return [
                'datas' => $rows,
                'pages' => 0,
                'page' => 0,
            ];
        }
        return $this->paginateMSI($rows, $currentPage, $limit);
    }

    /**
     * @return int|void
     */
    public function countDemandGoToEnd()
    {
        return sizeof($this->findDemandGoToEnd(0, 0)['datas']) ?? 0;
    }

    /**
     * @param int $currentPage
     * @param int $limit
     * @return array
     */
    public function findDemandOutOfTime(int $currentPage = 1, int $limit = 10): array
    {
        $date = (new DateTimeImmutable('now'))->modify("-2 weeks");
        $sql = "SELECT * FROM " . $this->dbName . " bcd ";
        $sql .= "WHERE bcd.created_at <= '" . $date->format('Y-m-d') . "' ";
        $sql .= "AND bcd.closed_at IS NULL ";
        $sql .= " ORDER BY bcd.created_at DESC";
        $stmt = $this->conn->query($sql);
        $rows = [];
        $client = new MySQLiCliInfos();
        if (mysqli_num_rows($stmt) > 0) {
            /** @var MSIDemandes $row */
            while($row = mysqli_fetch_object($stmt, $this->className)){
//                $row = $this->hydrate($row);
                $row->setClient($client->find((int) $row->getClientId()));
                $rows[$row->getId()] = $row;
            }
        }

        if ($currentPage === 0 && $limit === 0) {
            return [
                'datas' => $rows,
                'pages' => 0,
                'page' => 0,
            ];
        }
        return $this->paginateMSI($rows, $currentPage, $limit);
    }

    /**
     * @return int|void
     */
    public function countDemandOutOfTime()
    {
        return sizeof($this->findDemandOutOfTime(0, 0)['datas']) ?? 0;
    }
}
