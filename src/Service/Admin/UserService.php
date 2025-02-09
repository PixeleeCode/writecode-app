<?php

namespace App\Service\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * Met à jour les données d'un utilisateur.
     */
    public function update(User $user): bool
    {
        $this->em->persist($user);
        $this->em->flush();

        return true;
    }

    /**
     * Supprime un utilisateur.
     */
    public function remove(User $user): bool
    {
        $this->em->remove($user);
        $this->em->flush();

        return true;
    }
}
