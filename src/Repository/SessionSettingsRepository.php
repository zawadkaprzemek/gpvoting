<?php

namespace App\Repository;

use App\Entity\Polling;
use App\Entity\SessionSettings;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SessionSettings|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionSettings|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionSettings[]    findAll()
 * @method SessionSettings[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionSettingsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SessionSettings::class);
    }

    // /**
    //  * @return SessionSettings[] Returns an array of SessionSettings objects
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
    public function findOneBySomeField($value): ?SessionSettings
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getSessionSettings(Polling $polling)
    {
        return $this->createQueryBuilder('ss')
            ->andWhere('ss.polling = :polling')
            ->setParameter('polling',$polling)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

    }
}
