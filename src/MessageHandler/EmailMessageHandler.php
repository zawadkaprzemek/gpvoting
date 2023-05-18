<?php

namespace App\MessageHandler;

use App\Message\EmailMessage;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class EmailMessageHandler implements MessageHandlerInterface
{
    private MailerService $mailerService;
    private EntityManagerInterface $manager;

    public function __construct(MailerService $mailerService, EntityManagerInterface $manager)
    {
        $this->mailerService = $mailerService;
        $this->manager = $manager;
    }

    public function __invoke(EmailMessage $message)
    {
        $participant=$message->getParticipant();
        $this->mailerService->sendEmailWithPassword($participant->getEmail(),$participant->getPlainPass(),$participant->getHash());
    }
}
