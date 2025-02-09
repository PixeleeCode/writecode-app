<?php

namespace App\Twig;

use App\Service\PremiumService;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Vérifie que l'utilisateur est bien premium.
 */
class PremiumExtension extends AbstractExtension
{
    private PremiumService $premiumService;
    private TokenStorageInterface $tokenStorage;

    public function __construct(PremiumService $premiumService, TokenStorageInterface $tokenStorage)
    {
        $this->premiumService = $premiumService;
        $this->tokenStorage = $tokenStorage;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_premium', [$this, 'isPremium']),
        ];
    }

    public function isPremium(): bool
    {
        if (null === $this->tokenStorage->getToken()) {
            return false;
        }

        $user = $this->tokenStorage->getToken()->getUser();

        return $this->premiumService->isPremium($user);
    }
}
