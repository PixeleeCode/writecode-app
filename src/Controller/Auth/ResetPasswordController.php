<?php

namespace App\Controller\Auth;

use App\Entity\Other\UserPassword;
use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UserRepository;
use App\Service\ThrottlerService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

/**
 * Gestion du mot de passe oublié.
 *
 * @Route("/mot-de-passe")
 */
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    private ResetPasswordHelperInterface $resetPasswordHelper;
    private UserRepository $userRepository;
    private UserService $userService;

    public function __construct(
        ResetPasswordHelperInterface $resetPasswordHelper,
        UserRepository $userRepository,
        UserService $userService
    ) {
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->userRepository = $userRepository;
        $this->userService = $userService;
    }

    /**
     * Formulaire de récupération du mot de passe.
     *
     * @Route("/perdu", name="app_forgot_password_request")
     */
    public function request(Request $request, ThrottlerService $throttlerService): Response
    {
        $user = new User();
        $form = $this->createForm(ResetPasswordRequestFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $result = $throttlerService->check($request->getClientIp(), 'app_forgot_password_request');
            if (!$result) {
                $this->addFlash('reset_password_error', 'Trop d\'essais en peu de temps. Retentes dans 10 minutes');

                return $this->redirectToRoute('app_forgot_password_request');
            }

            return $this->processSendingPasswordResetEmail($user->getEmail());
        }

        return $this->render('auth/reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     *
     * @Route("/edit/{token<(.+)>}", name="app_reset_password")
     */
    public function reset(Request $request, string $token = null): Response
    {
        if ($token) {
            // We store the token in session and remove it from the URL, to avoid the URL being
            // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            throw $this->createNotFoundException('Aucun token trouvé dans l\'URL');
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', 'Oups ! Un souci avec la requête...');

            return $this->redirectToRoute('app_forgot_password_request');
        }

        // The token is valid; allow the user to change their password.
        $editUserPwd = new User();
        $form = $this->createForm(ChangePasswordFormType::class, $editUserPwd);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // A password reset token should be used only once, remove it.
            $this->resetPasswordHelper->removeResetRequest($token);

            $editPassword = new UserPassword();
            $editPassword->setPassword($editUserPwd->getPassword());
            /* @var User $user */
            $this->userService->editPassword($user, $editPassword, 3);

            // The session is cleaned up after the password has been changed.
            $this->cleanSessionAfterReset();

            $this->addFlash('success', 'Ton mot de passe à correctement été modifié !');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('auth/reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    /**
     * Envoi l'email contenant le lien de réinitialisation.
     */
    private function processSendingPasswordResetEmail(string $email): RedirectResponse
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if (!$user) {
            $this->addFlash('reset_password_error', 'Cette adresse email n\'existe pas');

            return $this->redirectToRoute('app_forgot_password_request');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', 'Oups ! Tu as déjà fait une demande...');

            return $this->redirectToRoute('app_forgot_password_request');
        }

        $this->userService->resetMail($user, $resetToken->getToken());

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);
        $this->addFlash('success', 'L\'email à correctement été envoyé');

        return $this->redirectToRoute('app_forgot_password_request');
    }
}
