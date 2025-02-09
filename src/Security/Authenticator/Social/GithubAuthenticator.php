<?php

namespace App\Security\Authenticator\Social;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\Authenticator\Social\Exception\NotVerifiedEmailException;
use App\Service\AuthService;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\GithubResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GithubAuthenticator extends AbstractSocialAuthenticator
{
    protected string $serviceName = 'github';

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
        $githubUser = $this->getResourceOwnerFromCredentials($accessToken)->toArray();

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($githubUser) {
                $user = $this->repository->findForOauth('github', $githubUser['id'], $githubUser['email']);
                if ($user) {
                    if (null === $user->getGithubId()) {
                        $user->setGithubId($githubUser['id']);
                        $this->em->flush();
                    }

                    return $user;
                }

                $user = (new User())
                    ->setGithubId($githubUser['id'])
                    ->setEmail($githubUser['email'])
                    ->setFirstname($githubUser['name'] ?? 'John Doe')
                    ->setRgpd(true)
                ;

                $this->em->persist($user);
                $this->em->flush();

                $this->sendWelcomeMail($githubUser['email'], $githubUser['name'] ?? 'John Doe');

                return $user;
            })
        );
    }

    /**
     * Récupère l'utilisateur à partir du AccessToken.
     */
    public function getResourceOwnerFromCredentials(AccessToken $credentials): GithubResourceOwner
    {
        /** @var GithubResourceOwner $githubUser */
        $githubUser = parent::getResourceOwnerFromCredentials($credentials);
        $response = HttpClient::create()->request(
            'GET',
            'https://api.github.com/user/emails',
            [
                'headers' => [
                    'authorization' => "token {$credentials->getToken()}",
                ],
            ]
        );

        $emails = json_decode($response->getContent(), true);
        foreach ($emails as $email) {
            if (true === $email['primary'] && true === $email['verified']) {
                $data = $githubUser->toArray();
                $data['email'] = $email['email'];

                return new GithubResourceOwner($data);
            }
        }

        throw new NotVerifiedEmailException();
    }
}
