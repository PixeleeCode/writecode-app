<?php

namespace App\MessageHandler;

use App\Entity\User;
use App\Message\MailMessage;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class MailMessageHandler implements MessageHandlerInterface
{
    private EntityManagerInterface $em;
    private MailService $mail;

    public function __construct(EntityManagerInterface $entityManager, MailService $mailService)
    {
        $this->em = $entityManager;
        $this->mail = $mailService;
    }

    public function __invoke(MailMessage $message): bool
    {
        $user = $this->em->find(User::class, $message->getUserId());
        if ($user instanceof User) {
            return $this->mail->sendEmail($message->getTemplateId(), [
                'emailTo' => $user->getEmail(),
                'attributes' => $message->getParams(),
            ]);
        }

        return false;
    }
}
