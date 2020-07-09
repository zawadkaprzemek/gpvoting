<?php

namespace App\EventListener;

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if($user instanceof User)
        {
            $user->setLastIP($event->getRequest()->getClientIp());
            $user->setLastVisit(new DateTime());
            $this->em->persist($user);
            $this->em->flush();
        }

    }
}