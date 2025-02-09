<?php

namespace App\Controller\Auth;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Service\AuthService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use LogicException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Gestion de la connexion et de l'inscription.
 */
class AuthController extends AbstractController
{
    private const SCOPES = [
        'github' => ['read:user', 'user:email'],
        'gitlab' => ['read_user', 'openid'],
        'discord' => ['identify', 'email'],
    ];

    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Valide l'adresse email d'un utilisateur.
     *
     * @Route("/validation/mail/{token}/{send?0}", name="validate_mail")
     */
    public function validateMail(User $user, ?int $send, Request $request): RedirectResponse
    {
        $token = $request->get('token');
        if (empty($token) || $token !== $user->getToken()) {
            $this->addFlash('error', 'Ce token n\'est pas valide');

            return $this->redirectToRoute('app_register');
        }

        // Renvoi le mail de confirmation
        if (1 === $send) {
            $user->setToken(md5(uniqid('', true)));
            $this->userService->update($user, true);
            $this->addFlash('success', 'Un nouvel email de confirmation est parti. Surveille ta boîte !');
        } else {
            $user->setToken(null);
            $this->userService->update($user);
            $this->addFlash('success', 'Ton adresse email à bien été validée. Merci !');
        }

        return $this->redirectToRoute('account_index');
    }

    /**
     * Inscription utilisateur.
     *
     * @Route("/inscription", name="app_register")
     */
    public function register(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('course_index');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->save($user);

            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * Connexion utilisateur.
     *
     * @Route("/connexion", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('course_index');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * Connexion depuis un réseau social.
     *
     * @Route("/connect/{service}", name="social_connect")
     */
    public function connectSocial(string $service, ClientRegistry $clientRegistry): RedirectResponse
    {
        $this->ensureServiceAccepted($service);
        $client = $clientRegistry->getClient($service);

        return $client->redirect(self::SCOPES[$service], []);
    }

    /**
     * Déconnexion d'un réseau social.
     *
     * @Route("/unlink/{service}", name="social_unlink")
     * @IsGranted("ROLE_USER")
     */
    public function disconnectSocial(string $service, AuthService $authService, EntityManagerInterface $em): RedirectResponse
    {
        $this->ensureServiceAccepted($service);
        $method = 'set'.ucfirst($service).'Id';

        $authService->getUser()->$method(null);
        $em->flush();

        $this->addFlash('success', "Votre compte a bien été dissocié de $service");

        return $this->redirectToRoute('account_index');
    }

    private function ensureServiceAccepted(string $service): void
    {
        if (!array_key_exists($service, self::SCOPES)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Déconnexion d'un utilisateur.
     *
     * @Route("/deconnexion", name="app_logout")
     */
    public function logout()
    {
        throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
