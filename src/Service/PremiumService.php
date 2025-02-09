<?php

namespace App\Service;

use App\Entity\Price;
use App\Entity\User;
use App\Repository\SubscriptionRepository;
use DateTimeImmutable;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PremiumService
{
    private RouterInterface $router;
    private SubscriptionRepository $subscriptionRepository;
    private StripeClient $stripe;

    public function __construct(ParameterBagInterface $parameterBag, RouterInterface $router, SubscriptionRepository $subscriptionRepository)
    {
        $this->router = $router;
        $this->subscriptionRepository = $subscriptionRepository;
        $this->stripe = new StripeClient($parameterBag->get('stripe_private_key'));
    }

    /**
     * Crée un customer Stripe et sauvegarde l'ID dans l'utilisateur.
     *
     * @throws ApiErrorException
     */
    public function createCustomer(User $user): User
    {
        if ($user->getStripeId()) {
            return $user;
        }

        $client = $this->stripe->customers->create([
            'metadata' => [
                'user_id' => (string) $user->getId(),
            ],
            'email' => $user->getEmail(),
            'name' => $user->getUserIdentifier(),
        ]);

        $user->setStripeId($client->id);

        return $user;
    }

    /**
     * Crée une session et renvoie l'URL du portail de gestion.
     *
     * @throws ApiErrorException
     */
    public function createPortalSession(string $customerId): string
    {
        $session = $this->stripe->billingPortal->sessions->create([
            'return_url' => $this->router->generate('account_invoices', [], UrlGenerator::ABSOLUTE_URL),
            'customer' => $customerId,
        ]);

        return $session->url;
    }

    /**
     * Récupère les 12 dernières factures d'un customer.
     *
     * @throws ApiErrorException
     */
    public function retrieveInvoicesCustomer(string $customerId): array
    {
        $session = $this->stripe->invoices->all([
            'limit' => 12,
            'customer' => $customerId,
        ]);

        return $session->data;
    }

    /**
     * Crée une session et renvoie l'URL de paiement.
     *
     * @throws ApiErrorException
     */
    public function createSuscriptionSession(User $user, Price $price): string
    {
        $session = $this->stripe->checkout->sessions->create([
            'success_url' => $this->router->generate('course_index', ['success' => 1], UrlGenerator::ABSOLUTE_URL),
            'cancel_url' => $this->router->generate('premium_index', [], UrlGenerator::ABSOLUTE_URL),
            'customer' => $user->getStripeId(),
            'billing_address_collection' => 'required',
            'payment_method_types' => ['card'],
            'subscription_data' => [
                'metadata' => [
                    'price_id' => $price->getId(),
                ],
            ],
            'line_items' => [
                [
                    'price' => $price->getPriceId(),
                    'quantity' => 1,
                ],
            ],
            'mode' => 'subscription',
        ]);

        return $session->id;
    }

    /**
     * Vérifie si l'utilisateur est premium.
     */
    public function isPremium(UserInterface $user): bool
    {
        $roles = ['ROLE_ADMIN', 'ROLE_MODERATOR'];
        if (count(array_intersect($roles, $user->getRoles())) > 0) {
            return true;
        }

        $now = new DateTimeImmutable();
        $subscription = $this->subscriptionRepository->findOneBy(['user' => $user]);

        return !(!$subscription || $now > $subscription->getDateEnd());
    }
}
