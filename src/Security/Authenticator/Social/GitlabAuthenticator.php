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

class GitlabAuthenticator extends AbstractSocialAuthenticator
{
    protected string $serviceName = 'gitlab';

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
                $gitlabUser = $client->fetchUserFromToken($accessToken)->toArray();
                $user = $this->repository->findForOauth('gitlab', (string) $gitlabUser['id'], $gitlabUser['email']);

                if ($user) {
                    if (null === $user->getGithubId()) {
                        $user->setGitlabId($gitlabUser['id']);
                        $this->em->flush();
                    }

                    return $user;
                }

                $user = (new User())
                    ->setGitlabId($gitlabUser['id'])
                    ->setEmail($gitlabUser['email'])
                    ->setFirstname($gitlabUser['username'] ?? 'John Doe')
                    ->setRgpd(true)
                ;

                $this->em->persist($user);
                $this->em->flush();

                $this->sendWelcomeMail($gitlabUser['email'], $gitlabUser['username'] ?? 'John Doe');

                return $user;
            })
        );
    }
}
