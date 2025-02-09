<?php

namespace App\Security\Authenticator\Social;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AuthService;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class DiscordAuthenticator extends AbstractSocialAuthenticator
{
    protected string $serviceName = 'discord';

    private UserRepository $repository;

    public function __construct(
        ClientRegistry $clientRegistry,
        EntityManagerInterface $em,
        RouterInterface $router,
        AuthService $authService,
        MailService $mailService,
        UserRepository $repository
    ) {
        parent::__construct($clientRegistry, $em, $router, $authService, $mailService);
        $this->repository = $repository;
    }

    public function authenticate(Request $request): PassportInterface
    {
        $client = $this->clientRegistry->getClient($this->serviceName);
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                $discordUser = $client->fetchUserFromToken($accessToken)->toArray();
                $user = $this->repository->findForOauth('discord', (string) $discordUser['id'], $discordUser['email']);

                if ($user) {
                    if (null === $user->getGithubId()) {
                        $user->setDiscordId($discordUser['id']);
                        $this->em->flush();
                    }

                    return $user;
                }

                $user = (new User())
                    ->setDiscordId($discordUser['id'])
                    ->setEmail($discordUser['email'])
                    ->setFirstname($discordUser['username'] ?? 'John Doe')
                    ->setRgpd(true)
                ;

                $this->em->persist($user);
                $this->em->flush();

                $this->sendWelcomeMail($discordUser['email'], $discordUser['username'] ?? 'John Doe');

                return $user;
            })
        );
    }
}
