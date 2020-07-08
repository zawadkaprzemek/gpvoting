<?php

namespace App\Repository;

use App\Entity\Polling;
use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Question|null find($id, $lockMode = null, $lockVersion = null)
 * @method Question|null findOneBy(array $criteria, array $orderBy = null)
 * @method Question[]    findAll()
 * @method Question[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    // /**
    //  * @return Question[] Returns an array of Question objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('q.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    public function getSort(Polling $polling)
    {
        return $this->createQueryBuilder('q')
            ->select('COUNT(q) as sort')
            ->andWhere('q.polling = :polling')
            ->setParameter('polling',$polling)
            ->getQuery()
            ->getSingleResult();
    }

    public function getPollingQuestions(Polling $polling)
    {
        return $this->createQueryBuilder('q')
            ->addSelect('a')
            ->innerJoin('q.answers','a')
            ->andWhere('q.polling = :polling')
            ->setParameter('polling',$polling)
            ->addOrderBy('q.sort','ASC')
            ->addOrderBy('q.createdAt','ASC')
            ->getQuery()
            ->getResult();
    }

    public function findQuestion(Polling $polling,int $sort)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.polling= :polling')
            ->setParameter('polling',$polling)
            ->andWhere('q.sort = :sort')
            ->setParameter('sort',$sort)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findQuestionsWithHiggerSort(Question $question)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.polling = :polling')
            ->setParameter('polling',$question->getPolling())
            ->andWhere('q.sort > :sort')
            ->setParameter('sort',$question->getSort())
            ->addOrderBy('q.sort')
            ->getQuery()
            ->getResult();
    }

    /*
    public function findOneBySomeField($value): ?Question
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
