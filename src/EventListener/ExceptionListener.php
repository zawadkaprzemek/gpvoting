<?php

namespace App\EventListener;


use App\Entity\ExceptionLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ExceptionListener
{
    private $em;
    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    public function __construct(EntityManagerInterface $em,TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelException(ExceptionEvent $event): int
    {
        $exception=$event->getThrowable();
        $log=new ExceptionLog();
        /** @var User|null $user */
        if($this->tokenStorage->getToken()!==null)
        {
            $user=$this->tokenStorage->getToken()->getUser();
            $log->setUserId($user->getId());
        }

        $log->setData(
          [
              'message'=>$exception->getMessage(),
              'file'=>$exception->getFile(),
              'line'=>$exception->getLine()
          ]
        );
        dump($exception);

        $this->em->persist($log);
        $this->em->flush();
        return 0;
    }
}