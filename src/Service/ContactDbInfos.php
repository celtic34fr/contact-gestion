<?php

namespace Celtic34fr\ContactGestion\Service;

use Celtic34fr\ContactCore\Traits\DbPaginateTrait;
use Celtic34fr\ContactGestion\Repository\ContactRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

/** service de gestion est restitution des informations pour le Widget ContactWidget */
class ContactDbInfos
{
    use DbPaginateTrait;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager,
    private ContactRepository $contactRepository)
    {
        $this->entityManager = $entityManager;
    }

    /** méthode de recherche des demandes de contact non clôturées */
    public function findRequestAll(int $currentPage = 1, int $limit = 10): array
    {
        $qb = $this->contactRepository
            ->createQueryBuilder("dem")
            ->where('dem.closed_at IS NULL')
            ->orderBy('dem.created_at', 'DESC')
            ->getQuery();
        return $this->paginateDoctrine($qb, $currentPage, $limit);
    }

    /** méthode décompte des demandes de contact non clôturées */
    public function countRequestAll(): int
    {
        return sizeof($this->findRequestAll(0, 0)['datas']) ?? 0;
    }

   /** méthode de recherche des demandes de contact sur les 2 dernières semaines */
   public function findLast2WeeksDemands(int $currentPage = 1, int $limit = 10): array
    {
        $date = (new DateTimeImmutable('now'))->modify("-2 weeks");
        $qb = $this->contactRepository
            ->createQueryBuilder("dem")
            ->where('dem.created_at > :date')
            ->orderBy('dem.created_at', 'DESC')
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery();
        return $this->paginateDoctrine($qb, $currentPage, $limit);
    }

   /** méthode décompte des demandes de contact sur les 2 dernières semaines */
   public function countLast2WeeksDemands(): int
    {
        return sizeof($this->findLast2WeeksDemands(0, 0)['datas']) ?? 0;
    }

   /** 
    * méthode de recherche des demandes de contact non clôturées arrivant au terme des 2 semaines 
    * de délais de prise en compte
    */
   public function findDemandGoToEnd(int $currentPage = 1, int $limit = 10): array
    {
        $dateDeb = (new DateTimeImmutable('now'))->modify("-2 weeks");
        $dateFin = $dateDeb->modify('+1 day');
        $qb = $this->contactRepository
            ->createQueryBuilder("dem")
            ->where('dem.created_at >= :dateDeb')
            ->andWhere('dem.created_at <= :dateFin')
            ->orderBy('dem.created_at', 'DESC')
            ->setParameter('dateDeb', $dateDeb->format('Y-m-d'))
            ->setParameter('dateFin', $dateFin->format('Y-m-d'))
            ->getQuery();
        return $this->paginateDoctrine($qb, $currentPage, $limit);
    }

   /** 
    * méthode décompte des demandes de contact non clôturées arrivant au terme des 2 semaines 
    * de délais de prise en compte
    */
    public function countDemandGoToEnd(): int
    {
        return sizeof($this->findDemandGoToEnd(0, 0)['datas']) ?? 0;
    }

   /** 
    * méthode de recherche des demandes de contact non clôturées ayant dépassées le terme des 2 semaines 
    * de délais de prise en compte
    */
    public function findDemandOutOfTime(int $currentPage = 1, int $limit = 10): array
    {
        $today = new DateTimeImmutable('now'); 
        $date = $today->modify("-2 weeks");
        $qb = $this->contactRepository
            ->createQueryBuilder("dem")
            ->where('dem.created_at <= :date')
            ->andWhere('dem.closed_at IS NULL')
            ->orderBy('dem.created_at', 'DESC')
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery();
        return $this->paginateDoctrine($qb, $currentPage, $limit);
    }

   /** 
    * méthode décompte des demandes de contact non clôturées ayant dépassées le terme des 2 semaines 
    * de délais de prise en compte
    */
    public function countDemandOutOfTime(): int
    {
        return sizeof($this->findDemandOutOfTime(0, 0)['datas']) ?? 0;
    }
}