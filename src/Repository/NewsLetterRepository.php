<?php

namespace Celtic34fr\ContactGestion\Repository;

use Celtic34fr\ContactGestion\Entity\NewsLetter;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NewsLetter>
 *
 * @method NewsLetter|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewsLetter|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewsLetter[]    findAll()
 * @method NewsLetter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsLetterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewsLetter::class);
    }

    public function save(NewsLetter $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(NewsLetter $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findMailingInfos()
    {
        $mailingInfos = [];
        $today = (new DateTime('now'))->format('Y-m-d') . ' 00:00:00';

        $qb = $this->createQueryBuilder('n')
            ->where('n.created_at IS NOT NULL')
            ->andWhere('n.created_at <= :today')
            ->andWhere('n.ended_at IS NULL')
            ->setParameter('today', $today)
            ->getQuery()
            ->getResult();

        if ($qb) {
            /** @var NewsLetter $item */
            foreach ($qb as $item) {
                $occurs = [];
                $client = $item->getClient();
                $occurs['courriel'] = $client->getCourriel();
                $cliInfos = $client->getCliInfos()->first();
                $occurs['nom'] = $cliInfos->getNom();
                $occurs['prenom'] = $cliInfos->getPrenom() ?? "";
                $occurs['telephone'] = $cliInfos->getTelephone() ?? "";
                $occurs['fullname'] = $cliInfos->getFullname();
                $mailingInfos[] = $occurs;
            }

            return $mailingInfos;
        }
        return false;
    }

    //    /**
    //     * @return NewsLetter[] Returns an array of NewsLetter objects
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

    //    public function findOneBySomeField($value): ?NewsLetter
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
