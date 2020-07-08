<?php

namespace App\Repository;

use App\Entity\Polling;
use App\Entity\SessionUsers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SessionUsers|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionUsers|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionUsers[]    findAll()
 * @method SessionUsers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionUsersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SessionUsers::class);
    }

    // /**
    //  * @return SessionUsers[] Returns an array of SessionUsers objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SessionUsers
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */


    public function checkUserOnTheList(string $name,string $ip, Polling $polling)
    {
        return $this->createQueryBuilder('su')
            ->andWhere('su.polling = :polling')
            ->andWhere('su.name = :name')
            ->andWhere('su.ip = :ip')
            ->setParameter('polling',$polling)
            ->setParameter('name',$name)
            ->setParameter('ip',$ip)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getPollingUsers(Polling $polling)
    {
        return $this->createQueryBuilder('su')
            ->andWhere('su.polling = :polling')
            ->setParameter('polling',$polling)
            ->andWhere('su.date > :date')
            ->setParameter('date', new \DateTime("-1 hour"))
            ->addOrderBy('su.date','DESC')
            ->getQuery()
            ->getResult()
            ;
    }
}
