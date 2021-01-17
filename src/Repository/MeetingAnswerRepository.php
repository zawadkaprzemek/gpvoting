<?php

namespace App\Repository;

use App\Entity\MeetingAnswer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MeetingAnswer|null find($id, $lockMode = null, $lockVersion = null)
 * @method MeetingAnswer|null findOneBy(array $criteria, array $orderBy = null)
 * @method MeetingAnswer[]    findAll()
 * @method MeetingAnswer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeetingAnswerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MeetingAnswer::class);
    }

    // /**
    //  * @return MeetingAnswer[] Returns an array of MeetingAnswer objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MeetingAnswer
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
