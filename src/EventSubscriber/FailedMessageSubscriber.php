<?php

namespace App\EventSubscriber;

use App\Message\FailedJob;
use App\Service\MailService;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Bridge\Doctrine\Transport\DoctrineReceivedStamp;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Stamp\RedeliveryStamp;

class FailedMessageSubscriber implements EventSubscriberInterface
{
    private MailService $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageFailedEvent::class => 'onMessageFailed',
        ];
    }

    /**
     * @throws Exception
     */
    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        // On reçoit une enveloppe de tâche "classique" et on veut la faire passer pour une tâche en échec
        // On lui passe un RedeliveryStamp (pour faire croire que la tâche a déjà été relancé)
        $redeliveryStamp = new RedeliveryStamp(1, $event->getThrowable()->getMessage());
        // On lui passe un DoctrineReceivedStamp (pour faire croire que la tâche provient de doctrine)
        $doctrineStamp = new DoctrineReceivedStamp('1');
        $enveloppe = $event->getEnvelope()->with($redeliveryStamp)->with($doctrineStamp);
        $job = new FailedJob($enveloppe);
        $this->mailService->notifyFailed('Une tâche de la file d\'attente a échoué', $job);
    }
}
