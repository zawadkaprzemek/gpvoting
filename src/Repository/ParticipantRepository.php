<?php

namespace App\Repository;

use App\Entity\Participant;
use App\Entity\ParticipantList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Participant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participant[]    findAll()
 * @method Participant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    // /**
    //  * @return Participant[] Returns an array of Participant objects
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
    public function findOneBySomeField($value): ?Participant
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getParticipantsFromList(ParticipantList $list)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.list = :list')
            ->setParameter('list',$list)
            ->getQuery()
            ->getResult()
            ;
    }

    public function checkCredentials(ParticipantList $list,array $data)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.list = :list')
            ->andWhere('p.email = :email')
            ->andWhere('p.password = :pass')
            ->setParameter('list',$list)
            ->setParameter('email',$data['email'])
            ->setParameter('pass',md5($data['password']))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function getParticipantByEmail(ParticipantList $list,string $email)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.list = :list')
            ->andWhere('p.email = :email')
            ->setParameter('list',$list)
            ->setParameter('email',$email)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    public function getParticipantByHash(string $hash)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.hash = :hash')
            ->setParameter('email',$hash)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    /**
     * @return Criteria
     */
    public static function createAcceptedParticipantsCriteria(): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('accepted',true))
            ;
    }
}
