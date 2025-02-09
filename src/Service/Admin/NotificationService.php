<?php

namespace App\Service\Admin;

use App\Message\MailMessage;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\MessageBusInterface;

class NotificationService
{
    private MessageBusInterface $messageBus;
    private UserRepository $userRepository;

    public function __construct(MessageBusInterface $messageBus, UserRepository $userRepository)
    {
        $this->messageBus = $messageBus;
        $this->userRepository = $userRepository;
    }

    /**
     * Notifie plusieurs utilisateurs.
     */
    public function sendNotifications(int $templateId, string $title = null): bool
    {
        $userAccepted = $this->userRepository->findBy(['notifications' => true]);
        foreach ($userAccepted as $user) {
            $params = [
                'FIRSTNAME' => $user->getFirstname(),
                'TITLE' => $title,
            ];

            $this->messageBus->dispatch(new MailMessage($user->getId(), $templateId, $params));
        }

        return true;
    }
}
