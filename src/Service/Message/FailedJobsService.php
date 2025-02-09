<?php

namespace App\Service\Message;

use App\Message\FailedJob;
use Exception;
use RuntimeException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;
use Symfony\Component\Messenger\Transport\Sync\SyncTransport;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Traversable;

class FailedJobsService
{
    private ListableReceiverInterface $receiver;
    private MessageBusInterface $messageBus;

    /**
     * @throws Exception
     */
    public function __construct(TransportInterface $receiver, MessageBusInterface $messageBus)
    {
        if (!($receiver instanceof ListableReceiverInterface)) {
            throw new Exception('Le service '.self::class.' attend un receiver de type '.ListableReceiverInterface::class);
        }

        $this->receiver = $receiver;
        $this->messageBus = $messageBus;
    }

    /**
     * @return FailedJob[]
     */
    public function getJobs(): array
    {
        if ($this->receiver instanceof SyncTransport) {
            return [];
        }
        $envelopes = $this->receiver->all();
        if ($envelopes instanceof Traversable) {
            $envelopes = iterator_to_array($envelopes);
        }

        return array_map(static fn (Envelope $envelope) => new FailedJob($envelope), $envelopes);
    }

    public function retryJob(int $jobId): void
    {
        $envelope = $this->receiver->find($jobId);
        if ($envelope instanceof Envelope) {
            $this->messageBus->dispatch($envelope->getMessage());
            $this->receiver->reject($envelope);
        } else {
            throw new RuntimeException("Impossible de trouver le job #{$jobId}");
        }
    }

    public function deleteJob(int $jobId): void
    {
        $envelope = $this->receiver->find($jobId);
        if ($envelope instanceof Envelope) {
            $this->receiver->reject($envelope);
        }
    }
}
