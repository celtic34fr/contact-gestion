<?php

namespace Bolt\Extension\Celtic34fr\ContactGestion\Repository;

use Bolt\Extension\Celtic34fr\ContactCore\Traits\DbPaginateTrait;
use Bolt\Extension\Celtic34fr\ContactGestion\Entity\Response;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Response>
 *
 * @method Response|null find($id, $lockMode = null, $lockVersion = null)
 * @method Response|null findOneBy(array $criteria, array $orderBy = null)
 * @method Response[]    findAll()
 * @method Response[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResponseRepository extends ServiceEntityRepository
{
    use DbPaginateTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Response::class);
    }

    public function save(Response $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Response $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /** méthode de l'ensemble des demandes de contact (questions) et/ou des réponses avec pagination */
    public function findQrPaginate(int $page = 1, $limit = 10): array
    {
        $results = [];
        $qb = $this->createQueryBuilder('cr')
            ->select('cr', 'cd')
            ->leftJoin('cr.contact', 'cd')
            ->orderBy('cr.closed_at', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        if ($qb) {
            /** @var Response $row */
            foreach ($qb as $row) {
                $results[$row->getClosedAt()->format('Ymd')] = [
                    'reponse' => $row->getReponse(),
                    'sujet' => $row->getContact()->getSujet(),
                    'created_at' => $row->getContact()->getCreatedAt(),
                    'closed_at' => $row->getClosedAt(),
                    'close_at' => $row->getClosedAt(),
                ];
            }
        }
        return $this->paginateManual($results, $page, $limit);
    }

//    /**
//     * @return Response[] Returns an array of Response objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Response
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
