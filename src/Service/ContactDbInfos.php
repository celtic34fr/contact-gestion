<?php

namespace Celtic34fr\ContactGestion\Service;

use Celtic34fr\ContactCore\Entity\Courriels;
use Celtic34fr\ContactCore\Trait\DbPaginateTrait;
use Celtic34fr\ContactGestion\Entity\Contacts;
use Celtic34fr\ContactGestion\Repository\ContactsRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class ContactDbInfos
{
    use DbPaginateTrait;

    private EntityManagerInterface $entityManager;
    private ContactsRepository $contactsRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->contactsRepository = $this->entityManager->getRepository(Contacts::class);
        $this->courrielsRepository = $this->entityManager->getRepository(Courriels::class);
    }

    public function findRequestAll(int $currentPage = 1, int $limit = 10): array
    {
        $qb = $this->contactsRepository
            ->createQueryBuilder("dem")
            ->where('dem.closed_at IS NULL')
            ->orderBy('dem.created_at', 'DESC')
            ->getQuery();
        return $this->paginateDoctrine($qb, $currentPage, $limit);
    }

    public function countRequestAll(): int
    {
        return sizeof($this->findRequestAll(0, 0)['datas']) ?? 0;
    }

    public function findLast2WeeksDemands(int $currentPage = 1, int $limit = 10): array
    {
        $date = (new DateTimeImmutable('now'))->modify("-2 weeks");
        $qb = $this->contactsRepository
            ->createQueryBuilder("dem")
            ->where('dem.created_at > :date')
            ->orderBy('dem.created_at', 'DESC')
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery();
        return $this->paginateDoctrine($qb, $currentPage, $limit);
    }

    public function countLast2WeeksDemands(): int
    {
        return sizeof($this->findLast2WeeksDemands(0, 0)['datas']) ?? 0;
    }

    public function findDemandGoToEnd(int $currentPage = 1, int $limit = 10): array
    {
        $dateDeb = (new DateTimeImmutable('now'))->modify("-2 weeks");
        $dateFin = $dateDeb->modify('+1 day');
        $qb = $this->contactsRepository
            ->createQueryBuilder("dem")
            ->where('dem.created_at >= :dateDeb')
            ->andWhere('dem.created_at <= :dateFin')
            ->orderBy('dem.created_at', 'DESC')
            ->setParameter('dateDeb', $dateDeb->format('Y-m-d'))
            ->setParameter('dateFin', $dateFin->format('Y-m-d'))
            ->getQuery();
        return $this->paginateDoctrine($qb, $currentPage, $limit);
    }

    public function countDemandGoToEnd(): int
    {
        return sizeof($this->findDemandGoToEnd(0, 0)['datas']) ?? 0;
    }

    public function findDemandOutOfTime(int $currentPage = 1, int $limit = 10): array
    {
        $date = (new DateTimeImmutable('now'))->modify("-2 weeks");
        $qb = $this->contactsRepository
            ->createQueryBuilder("dem")
            ->where('dem.created_at >= :date')
            ->andWhere('dem.closed_at IS NULL')
            ->orderBy('dem.created_at', 'DESC')
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery();
        return $this->paginateDoctrine($qb, $currentPage, $limit);
    }

    public function countDemandOutOfTime(): int
    {
        return sizeof($this->findDemandOutOfTime(0, 0)['datas']) ?? 0;
    }
}