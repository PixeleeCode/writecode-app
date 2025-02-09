<?php

namespace App\Service\Admin\Premium;

use App\Entity\Price;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class StripeService
{
    private ParameterBagInterface $parameter;
    private StripeClient $stripe;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameter = $parameterBag;
        $this->stripe = new StripeClient($this->parameter->get('stripe_private_key'));
    }

    /**
     * Enregistre un nouveau prix.
     *
     * @throws ApiErrorException
     */
    public function save(Price $price): string
    {
        $subscription = $this->stripe->prices->create([
            'unit_amount' => (float) $price->getAmount() * 100,
            'currency' => 'eur',
            'recurring' => [
                'interval' => $price->getRecurring(),
            ],
            'product' => $this->parameter->get('stripe_product_id'),
        ]);

        return $subscription->id;
    }

    /**
     * Met à jour un prix.
     *
     * @throws ApiErrorException
     */
    public function update(Price $price): string
    {
        $subscription = $this->stripe->prices->update(
            $price->getPriceId(),
            [
                'active' => !$price->getIsVisible(),
            ]
        );

        return $subscription->id;
    }
}
