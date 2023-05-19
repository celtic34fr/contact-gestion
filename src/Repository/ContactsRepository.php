<?php

namespace Celtic34fr\ContactGestion\Repository;

use Celtic34fr\ContactGestion\Entity\Contacts;
use Celtic34fr\ContactGestion\Trait\DbPaginateTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contacts>
 *
 * @method Contacts|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contacts|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contacts[]    findAll()
 * @method Contacts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContactsRepository extends ServiceEntityRepository
{
    use DbPaginateTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contacts::class);
    }

    public function save(Contacts $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Contacts $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findRequestAll(int $currentPage = 1, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('bco')
            ->where('bco.closed_at IS NULL')
            ->orderBy('bco.created_at', 'DESC')
            ->orderBy('bco.id', 'DESC')
            ->getQuery();
        return $this->paginateDoctrine($qb, $currentPage, $limit);
    }

    /**
     * @throws \Exception
     */
    public function findMatchText(mixed $words): mixed
    {
        if (is_array($words)) {
            $words = implode(' ', (array) $words);
        }
        if (!is_string($words)) {
            throw new \Exception('paramètre incompatible : type string & array, type actuel '.gettype($words));
        }
        $query = $this->createQueryBuilder('cd');
        if (null != $words) {
            $query->andWhere('MATCH_AGAINST(cd.sujet, cd.demande) AGAINST (:mots boolean)>0')
                ->setParameter('mots', $words);
        }
        return $query->getQuery()->getResult();
    }

//    /**
//     * @return CRMDemandes[] Returns an array of Contacts objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Contacts
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
