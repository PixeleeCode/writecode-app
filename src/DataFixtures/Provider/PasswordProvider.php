<?php

namespace App\DataFixtures\Provider;

use App\Entity\User;
use Faker\Generator;
use Faker\Provider\Base as BaseProvider;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Permet de générer un mot de passe encodé pour les fixtures.
 */
final class PasswordProvider extends BaseProvider
{
    private UserPasswordHasherInterface $encoder;

    public function __construct(Generator $generator, UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
        parent::__construct($generator);
    }

    public function password(string $plainPassword): string
    {
        return $this->encoder->hashPassword(new User(), $plainPassword);
    }
}
