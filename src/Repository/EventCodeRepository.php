<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\EventCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EventCode|null find($id, $lockMode = null, $lockVersion = null)
 * @method EventCode|null findOneBy(array $criteria, array $orderBy = null)
 * @method EventCode[]    findAll()
 * @method EventCode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventCode::class);
    }

    // /**
    //  * @return EventCode[] Returns an array of EventCode objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function getEventCodesQuery(Event $event)
    {
        return $this->createQueryBuilder('ec')
            ->andWhere('ec.event= :event')
            ->setParameter('event',$event)
            ->addOrderBy('ec.name')
            ;
    }

    /*
    public function findOneBySomeField($value): ?EventCode
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
