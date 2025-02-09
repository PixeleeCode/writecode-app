<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Admin\UserService;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Gestion des utilisateurs en administration.
 *
 * @IsGranted("ROLE_ADMIN")
 * @Route("/admin/users", name="admin_user_")
 */
class UserController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Liste des utilisateurs.
     *
     * @Route("/", name="list")
     */
    public function index(UserRepository $userRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $users = $paginator->paginate(
            $userRepository->findBy([], ['created_at' => 'DESC']),
            0 === $page ? 1 : $page,
            20
        );

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * Suppression d'un utilisateur.
     *
     * @Route("/delete/{id}", name="delete", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function delete(User $user, Request $request): RedirectResponse
    {
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('delete-user', $submittedToken)) {
            $this->userService->remove($user);
            $this->addFlash('success', "L'utilisateur \"{$user->getUserIdentifier()}\" à bien été supprimée");
        }

        return $this->redirectToRoute('admin_user_list');
    }
}
