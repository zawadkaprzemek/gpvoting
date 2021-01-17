<?php

namespace App\Repository;

use App\Entity\GeneralMeeting;
use App\Entity\MeetingVoting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MeetingVoting|null find($id, $lockMode = null, $lockVersion = null)
 * @method MeetingVoting|null findOneBy(array $criteria, array $orderBy = null)
 * @method MeetingVoting[]    findAll()
 * @method MeetingVoting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeetingVotingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MeetingVoting::class);
    }

    // /**
    //  * @return MeetingVoting[] Returns an array of MeetingVoting objects
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
    public function findOneBySomeField($value): ?MeetingVoting
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function getVotingBySort(GeneralMeeting $meeting, int $sort)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.meeting = :meeting')
            ->andWhere('m.sort = :sort')
            ->setParameter('meeting',$meeting)
            ->setParameter('sort',$sort)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
