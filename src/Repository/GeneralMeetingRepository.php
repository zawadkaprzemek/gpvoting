<?php

namespace App\Repository;

use App\Entity\GeneralMeeting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GeneralMeeting|null find($id, $lockMode = null, $lockVersion = null)
 * @method GeneralMeeting|null findOneBy(array $criteria, array $orderBy = null)
 * @method GeneralMeeting[]    findAll()
 * @method GeneralMeeting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GeneralMeetingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GeneralMeeting::class);
    }

    // /**
    //  * @return GeneralMeeting[] Returns an array of GeneralMeeting objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GeneralMeeting
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
