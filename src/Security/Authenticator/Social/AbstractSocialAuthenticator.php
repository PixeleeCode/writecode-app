<?php

namespace App\Security\Authenticator\Social;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\Authenticator\Social\Exception\UserAuthenticatedException;
use App\Security\Authenticator\Social\Exception\UserOauthNotFoundException;
use App\Service\AuthService;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

abstract class AbstractSocialAuthenticator extends OAuth2Authenticator
{
    use TargetPathTrait;

    protected string $serviceName = '';
    protected ClientRegistry $clientRegistry;
    protected EntityManagerInterface $em;
    private RouterInterface $router;
    private AuthService $authService;
    private MailService $mail;

    public function __construct(
        ClientRegistry $clientRegistry,
        EntityManagerInterface $em,
        RouterInterface $router,
        AuthService $authService,
        MailService $mailService
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
        $this->authService = $authService;
        $this->mail = $mailService;
    }

    public function supports(Request $request): bool
    {
        if ('' === $this->serviceName) {
            throw new RuntimeException("You must set a \$serviceName property (for instance 'github', 'gitlab' and 'discord')");
        }

        return 'oauth_check' === $request->attributes->get('_route') && $request->get('service') === $this->serviceName;
    }

    public function start(): RedirectResponse
    {
        return new RedirectResponse($this->router->generate('app_login'));
    }

    public function getCredentials(): AccessTokenInterface
    {
        return $this->fetchAccessToken($this->getClient());
    }

    public function getUser(AccessToken $credentials): ?User
    {
        $resourceOwner = $this->getResourceOwnerFromCredentials($credentials);
        $user = $this->authService->getUserOrNull();
        if ($user) {
            throw new UserAuthenticatedException($user, $resourceOwner);
        }
        /** @var UserRepository $repository */
        $repository = $this->em->getRepository(User::class);
        $user = $this->getUserFromResourceOwner($resourceOwner, $repository);
        if (null === $user) {
            throw new UserOauthNotFoundException($resourceOwner);
        }

        return $user;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        if ($exception instanceof UserAuthenticatedException) {
            return new RedirectResponse($this->router->generate('account_password'));
        }

        if ($request->hasSession()) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }

        return new RedirectResponse($this->router->generate('app_login'));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): RedirectResponse
    {
        // On force le remember me pour déclencher le AbstractRememberMeServices (en attendant mieux)
        $request->request->set('_remember_me', '1');

        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->router->generate('course_index'));
    }

    protected function getResourceOwnerFromCredentials(AccessToken $credentials): ResourceOwnerInterface
    {
        return $this->getClient()->fetchUserFromToken($credentials);
    }

    protected function getUserFromResourceOwner(ResourceOwnerInterface $resourceOwner, UserRepository $repository): ?User
    {
        return null;
    }

    protected function getClient(): OAuth2ClientInterface
    {
        /* @var OAuth2Client $client */
        return $this->clientRegistry->getClient($this->serviceName);
    }

    protected function sendWelcomeMail(?string $email, ?string $username)
    {
        $this->mail->sendEmail(14, [
            'emailTo' => $email,
            'attributes' => [
                'FIRSTNAME' => $username,
            ],
        ]);
    }
}
