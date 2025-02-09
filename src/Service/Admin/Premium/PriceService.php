<?php

namespace App\Service\Admin\Premium;

use App\Entity\Price;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PriceService
{
    private EntityManagerInterface $em;
    private StripeService $stripeService;
    private ParameterBagInterface $parameter;

    public function __construct(EntityManagerInterface $entityManager, StripeService $stripeService, ParameterBagInterface $parameterBag)
    {
        $this->em = $entityManager;
        $this->stripeService = $stripeService;
        $this->parameter = $parameterBag;
    }

    /**
     * Enregistre un nouvel abonnement.
     *
     * @throws ApiErrorException
     */
    public function save(Price $price): bool
    {
        $priceId = $this->stripeService->save($price);
        $price->setPriceId($priceId);
        $price->setProductId($this->parameter->get('stripe_product_id'));

        $this->em->persist($price);
        $this->em->flush();

        return true;
    }

    /**
     * Met à jour un abonnement.
     *
     * @throws ApiErrorException
     */
    public function update(Price $price): bool
    {
        $priceId = $this->stripeService->update($price);
        $price->setPriceId($priceId);
        $price->setIsVisible(!$price->getIsVisible());

        $this->em->persist($price);
        $this->em->flush();

        return true;
    }

    /**
     * Met à jour le message promotionnel d'un abonnement.
     */
    public function updatePromote(Price $price): bool
    {
        $this->em->persist($price);
        $this->em->flush();

        return true;
    }
}
