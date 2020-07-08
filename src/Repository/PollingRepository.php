<?php

namespace App\Repository;

use App\Entity\Polling;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Polling|null find($id, $lockMode = null, $lockVersion = null)
 * @method Polling|null findOneBy(array $criteria, array $orderBy = null)
 * @method Polling[]    findAll()
 * @method Polling[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PollingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Polling::class);
    }

    // /**
    //  * @return Polling[] Returns an array of Polling objects
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
    public function findOneBySomeField($value): ?Polling
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findPollingsWithCode(string $code, \DateTime $now)
    {
        return $this->createQueryBuilder('p')
            ->join('p.code','c')
            ->andWhere('c.name = :code')
            ->andWhere('p.open = :open')
            ->andWhere('p.endDate > :now')
            ->setParameter('code',$code)
            ->setParameter('open',true)
            ->setParameter('now',$now)
            ->getQuery()
            ->getResult();
    }

    public function findByEventPassword(string $pass)
    {
        return $this->createQueryBuilder('p')
            ->join('p.room','r')
            ->join('r.event','e')
            ->andWhere('e.password = :pass')
            ->setParameter('pass',$pass)
            ->getQuery()
            ->getResult();
    }
}
