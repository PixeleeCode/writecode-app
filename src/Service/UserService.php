<?php

namespace App\Service;

use App\Entity\Other\UserPassword;
use App\Entity\User;
use App\Message\MailMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserService
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordEncoder;
    private MessageBusInterface $messageBus;
    private UrlGeneratorInterface $router;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordEncoder,
        MessageBusInterface $messageBus,
        UrlGeneratorInterface $router
    ) {
        $this->em = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->messageBus = $messageBus;
        $this->router = $router;
    }

    /**
     * Enregistre un utilisateur.
     */
    public function save(User $user): bool
    {
        $user->setToken(md5(uniqid('', true)));
        $user->setPassword(
            $this->passwordEncoder->hashPassword(
                $user,
                $user->getPassword()
            )
        );

        $this->em->persist($user);
        $this->em->flush();

        // Envoi l'email de validation
        $this->sendMailValidation($user);

        return true;
    }

    /**
     * Modifie le mot de passe utilisateur.
     */
    public function editPassword(User $user, UserPassword $userPassword, int $templateId = 4): bool
    {
        $user->setPassword(
            $this->passwordEncoder->hashPassword(
                $user,
                $userPassword->getPassword()
            )
        );

        $this->em->persist($user);
        $this->em->flush();

        $params = [];
        if (4 === $templateId) {
            $params = ['FIRSTNAME' => $user->getFirstname()];
        }

        $this->messageBus->dispatch(new MailMessage($user->getId(), $templateId, $params));

        return true;
    }

    /**
     * Supprime un compte utilisateur.
     */
    public function remove(User $user): bool
    {
        $this->em->remove($user);
        $this->em->flush();

        $session = new Session();
        $session->invalidate();

        return true;
    }

    /**
     * Met à jour les données d'un utilisateur.
     */
    public function update(User $user, bool $resend = false): bool
    {
        $this->em->persist($user);
        $this->em->flush();

        // Envoi l'email de validation si besoin.
        if ($resend) {
            $this->sendMailValidation($user);
        }

        return true;
    }

    /**
     * Envoi l'email de validation.
     */
    private function sendMailValidation(User $user): void
    {
        $params = [
            'FIRSTNAME' => $user->getFirstname(),
            'URL' => $this->router->generate('validate_mail', ['token' => $user->getToken()], UrlGeneratorInterface::ABSOLUTE_URL),
        ];

        $this->messageBus->dispatch(new MailMessage($user->getId(), 1, $params));
    }

    /**
     * Envoi un email contenant un lien de réinitialisation.
     */
    public function resetMail(User $user, string $token): void
    {
        $params = [
            'FIRSTNAME' => $user->getFirstname(),
            'URL' => $this->router->generate('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL),
        ];

        $this->messageBus->dispatch(new MailMessage($user->getId(), 2, $params));
    }
}
