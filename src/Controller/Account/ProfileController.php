<?php

namespace App\Controller\Account;

use App\Entity\Other\UserDelete;
use App\Entity\Other\UserPassword;
use App\Entity\User;
use App\Form\Account\ProfileFormType;
use App\Form\Account\UserDeleteFormType;
use App\Form\Account\UserPasswordFormType;
use App\Service\PremiumService;
use App\Service\UserService;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Gestion du compte client.
 *
 * @Route("/compte", name="account_")
 */
class ProfileController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Modifications des informations personnelles et suppression de compte.
     *
     * @Route("/", name="index")
     */
    public function index(Request $request): Response
    {
        // Modifications des informations.
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->update($user);

            $this->addFlash('success', 'Les modifications ont bien été enregistrées');
        }

        // Suppression de compte.
        $userDelete = new UserDelete();
        $formDelete = $this->createForm(UserDeleteFormType::class, $userDelete);
        $formDelete->handleRequest($request);

        if ($formDelete->isSubmitted() && $formDelete->isValid()) {
            $this->userService->remove($user);

            return $this->redirectToRoute('app_logout');
        }

        // Si une erreur est survenue au moment de la suppression du compte : Mot de passe invalide.
        if ($formDelete->isSubmitted() && !$formDelete->isValid()) {
            $errorDelete = $formDelete['password']->getErrors()->getChildren()->getMessage();
        }

        return $this->render('account/index.html.twig', [
            'form' => $form->createView(),
            'formDelete' => $formDelete->createView(),
            'errorDelete' => $errorDelete ?? '',
        ]);
    }

    /**
     * Modifications du mot de passe.
     *
     * @Route("/password", name="password")
     */
    public function password(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $userPassword = new UserPassword();
        $form = $this->createForm(UserPasswordFormType::class, $userPassword);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->editPassword($user, $userPassword);

            $this->addFlash('success', 'Les modifications ont bien été enregistrées');
            $form = $this->createForm(UserPasswordFormType::class, $userPassword);
        }

        return $this->render('account/password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Liste des factures.
     *
     * @Route("/factures", name="invoices")
     *
     * @throws ApiErrorException
     */
    public function invoices(PremiumService $premiumService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->getStripeId()) {
            $pourtalUrl = $premiumService->createPortalSession($user->getStripeId());
            $invoices = $premiumService->retrieveInvoicesCustomer($user->getStripeId());
        }

        return $this->render('account/invoices.html.twig', [
            'invoices' => $invoices ?? null,
            'portal' => $pourtalUrl ?? null,
        ]);
    }
}
