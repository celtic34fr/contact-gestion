<?php

namespace Celtic34fr\ContactGestion\Repository;

use Doctrine\Persistence\ManagerRegistry;
use Celtic34fr\ContactGestion\Entity\Responses;
use Celtic34fr\ContactCore\Trait\DbPaginateTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Responses>
 *
 * @method Responses|null find($id, $lockMode = null, $lockVersion = null)
 * @method Responses|null findOneBy(array $criteria, array $orderBy = null)
 * @method Responses[]    findAll()
 * @method Responses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResponsesRepository extends ServiceEntityRepository
{
    use DbPaginateTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Responses::class);
    }

    public function save(Responses $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Responses $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws \Exception
     */
    public function findMatchText($words, ?array $categories = null): mixed
    {
        if (is_array($words)) {
            $words = implode(' ', (array) $words);
        }
        if (!is_string($words)) {
            throw new \Exception('paramètre incompatible : type string & array, type actuel '.gettype($words));
        }
        $query = $this->createQueryBuilder('cr');
        if (null != $words) {
            $query->andWhere('MATCH_AGAINST(cr.reponse) AGAINST (:mots boolean)>0')
                ->setParameter('mots', $words);
        }
        if (null != $categories) {
            $query->leftJoin('cr.categories', 'c');
            $query->andWhere('c.id = :id')
                ->setParameter('id', $categories);
        }
        return $query->getQuery()->getResult();
    }

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
            /** @var Responses $row */
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
//     * @return Responses[] Returns an array of Responses objects
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

//    public function findOneBySomeField($value): ?Responses
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
