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
        switch ($message->getType()){
            case 'participant_credentials_mail':
                $this->mailerService->sendEmailWithPassword($participant->getEmail(),$participant->getPlainPass(),$participant->getHash());
                break;
            case 'participant_verify_mail':
                $this->mailerService->sendVerifyEmail($participant->getEmail(),$participant->getHash());
                break;
            default:
                break;

        }
    }
}
