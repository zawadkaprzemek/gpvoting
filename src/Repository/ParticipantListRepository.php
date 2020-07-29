<?php

namespace App\Repository;

use App\Entity\ParticipantList;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ParticipantList|null find($id, $lockMode = null, $lockVersion = null)
 * @method ParticipantList|null findOneBy(array $criteria, array $orderBy = null)
 * @method ParticipantList[]    findAll()
 * @method ParticipantList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipantListRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParticipantList::class);
    }

    // /**
    //  * @return ParticipantList[] Returns an array of ParticipantList objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ParticipantList
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getUsersLists(User $user)
    {
        return $this->createQueryBuilder('pl')
            ->andWhere('pl.user = :user')
            ->setParameter('user',$user)
            ->addOrderBy('pl.createdAt','desc')
            ->getQuery()
            ->getResult();
    }
}
