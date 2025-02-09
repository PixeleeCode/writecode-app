<?php

namespace App\Controller\Api;

use App\Entity\Price;
use App\Entity\Subscription;
use App\Entity\User;
use App\Message\MailMessage;
use App\Service\PremiumService;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Exception;
use Stripe\Event;
use Stripe\Exception\ApiErrorException;
use Stripe\Subscription as StripeSubscription;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Gestion des abonnements de Stripe.
 */
class StripeWebhookController extends AbstractController
{
    private EntityManagerInterface $em;
    private MessageBusInterface $messageBus;
    private PremiumService $premiumService;

    public function __construct(EntityManagerInterface $entityManager, MessageBusInterface $messageBus, PremiumService $premiumService)
    {
        $this->em = $entityManager;
        $this->messageBus = $messageBus;
        $this->premiumService = $premiumService;
    }

    /**
     * @Route("/stripe/webhook", name="stripe_webhook", methods={"POST"})
     *
     * @throws Exception
     */
    public function index(): JsonResponse
    {
        $payload = @file_get_contents('php://input');
        $event = Event::constructFrom(
            json_decode($payload, true, 512, JSON_THROW_ON_ERROR)
        );

        switch ($event->type) {
            case 'customer.subscription.created':
                return $this->onSubscriptionCreated($event->data['object']);
            case 'customer.subscription.updated':
                return $this->onSubscriptionUpdated($event->data['object']);
            case 'customer.subscription.deleted':
                return $this->onSubscriptionDeleted($event->data['object']);
            default:
                return $this->json([]);
        }
    }

    /**
     * Génère un ID pour le lien d'abonnement Stripe.
     *
     * @Route("/stripe/webhook/subscription/{id}", name="stripe_webhook_subscription", methods={"POST"}, requirements={"id"="\d+"})
     *
     * @throws ApiErrorException
     */
    public function createSessionSubscription(Price $price): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->getStripeId()) {
            $user = $this->premiumService->createCustomer($user);
            $this->em->persist($user);
            $this->em->flush();
        }

        $sessionId = $this->premiumService->createSuscriptionSession($user, $price);

        return new JsonResponse($sessionId);
    }

    /**
     * Enregistre un nouvel abonnement.
     *
     * @throws Exception
     */
    public function onSubscriptionCreated(StripeSubscription $stripeSubscription): JsonResponse
    {
        $price = $this->em->getRepository(Price::class)->find($stripeSubscription->metadata['price_id']);
        if (null === $price) {
            throw new NoResultException();
        }

        $endDate = new DateTimeImmutable();
        $duration = 'month' === $price->getRecurring() ? 1 : 12;
        $user = $this->getUserFromCustomer((string) $stripeSubscription->customer);

        $subscription = (new Subscription())
            ->setUser($user)
            ->setPrice($price)
            ->setSubscriptionId($stripeSubscription->id)
            ->setNextPayment(new DateTimeImmutable("@{$stripeSubscription->current_period_end}"))
            ->setDateEnd($endDate->add(new DateInterval("P{$duration}M")))
            ->setIsActive(true)
            ->setCreatedAt(new DateTimeImmutable())
        ;

        $this->em->persist($subscription);
        $this->em->flush();

        $params = ['FIRSTNAME' => $user->getFirstname()];
        $this->messageBus->dispatch(new MailMessage($user->getId(), 7, $params));

        return $this->json([]);
    }

    /**
     * Met à jour un abonnement.
     *
     * @throws Exception
     */
    public function onSubscriptionUpdated(StripeSubscription $stripeSubscription): JsonResponse
    {
        $subscription = $this->em->getRepository(Subscription::class)->findOneBy(['subscription_id' => $stripeSubscription->id]);
        if (!($subscription instanceof Subscription)) {
            throw new Exception("Impossible de trouver l'abonnement correspondant");
        }

        if ($stripeSubscription->cancel_at_period_end) {
            $subscription->setIsActive(false);
        } else {
            $subscription->setIsActive(true);
            $subscription->setNextPayment(new DateTimeImmutable("@{$stripeSubscription->current_period_end}"));
            $subscription->setDateEnd(new DateTimeImmutable("@{$stripeSubscription->current_period_end}"));
        }

        $this->em->flush();

        if ($stripeSubscription->cancel_at_period_end) {
            $user = $this->getUserFromCustomer((string) $stripeSubscription->customer);
            $date = new DateTimeImmutable("@{$stripeSubscription->current_period_end}");
            $dateEnd = $date->format('d-m-Y');
            $params = ['FIRSTNAME' => $user->getFirstname(), 'DATE_END' => $dateEnd];
            $this->messageBus->dispatch(new MailMessage($user->getId(), 8, $params));
        }

        return $this->json([]);
    }

    /**
     * Supprime un abonnement.
     *
     * @throws Exception
     */
    public function onSubscriptionDeleted(StripeSubscription $stripeSubscription): JsonResponse
    {
        $subscription = $this->em->getRepository(Subscription::class)->findOneBy(['subscription_id' => $stripeSubscription->id]);
        if (!($subscription instanceof Subscription)) {
            throw new Exception("Impossible de trouver l'abonnement correspondant");
        }

        $this->em->remove($subscription);
        $this->em->flush();

        $user = $this->getUserFromCustomer((string) $stripeSubscription->customer);
        $params = ['FIRSTNAME' => $user->getFirstname()];
        $this->messageBus->dispatch(new MailMessage($user->getId(), 9, $params));

        return $this->json([]);
    }

    /**
     * Retrouve un utilisateur selon son CustomerID.
     *
     * @throws Exception
     */
    private function getUserFromCustomer(string $customerId): User
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['stripe_id' => $customerId]);
        if (null === $user) {
            throw new Exception("Impossible de trouver l'utilisateur correspondant au paiement");
        }

        return $user;
    }
}
