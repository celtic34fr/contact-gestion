<?php

namespace Celtic34fr\ContactGestion\Repository;

use Celtic34fr\ContactGestion\Entity\Demandes;
use Celtic34fr\ContactCore\Trait\DbPaginateTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @extends ServiceEntityRepository<Demandes>
 *
 * @method Demandes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Demandes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Demandes[]    findAll()
 * @method Demandes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DemandesRepository extends ServiceEntityRepository
{
    use DbPaginateTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Demandes::class);
    }

    public function save(Demandes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Demandes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    #[ArrayShape(['datas' => "\Doctrine\ORM\Tools\Pagination\Paginator", 'pages' => "\int|null", 'page' => "\int|mixed"])]
    public function findRequestAll(int $currentPage = 1, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder("bco")
            ->where('bco.closed_at IS NULL')
            ->orderBy('bco.created_at', 'DESC')
            ->getQuery();
        return $this->paginateDoctrine($qb, $currentPage, $limit);
    }

    /**
     * @param mixed $words
     * @return mixed
     * @throws Exception
     */
    public function findMatchText(mixed $words): mixed
    {
        if (is_array($words)) $words = implode(" ", (array) $words);
        if (!is_string($words))
            throw new Exception("paramètre incompatible : type string & array, type actuel ".gettype($words));
        $query = $this->createQueryBuilder('cd');
        if($words != null){
            $query->andWhere('MATCH_AGAINST(cd.sujet, cd.demande) AGAINST (:mots boolean)>0')
                ->setParameter('mots', $words);
        }
        return $query->getQuery()->getResult();
    }

//    /**
//     * @return CRMDemandes[] Returns an array of CRMDemandes objects
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

//    public function findOneBySomeField($value): ?CRMDemandes
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
