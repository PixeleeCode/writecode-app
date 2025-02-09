<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Service pour simplifier la communication avec l'authentification et offrir un type plus strict.
 */
class AuthService
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getUser(): User
    {
        $user = $this->getUserOrNull();
        if (null === $user) {
            throw new AccessDeniedException();
        }

        return $user;
    }

    public function getUserOrNull(): ?User
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return null;
        }

        $user = $token->getUser();
        if (!\is_object($user)) {
            return null;
        }

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }
}
