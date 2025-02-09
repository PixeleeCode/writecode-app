<?php

namespace App\Controller\Admin;

use App\Service\Message\FailedJobsService;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Gestion des tâches échouées en administration.
 *
 * @IsGranted("ROLE_ADMIN")
 * @Route("/admin/messenger", name="admin_messenger_")
 */
class MessengerController extends AbstractController
{
    /**
     * Liste des tâches échouées du messenger.
     *
     * @Route("/", name="index")
     */
    public function index(FailedJobsService $failedJobsService, PaginatorInterface $paginator, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $jobs = $paginator->paginate(
            $failedJobsService->getJobs(),
            0 === $page ? 1 : $page,
            20
        );

        return $this->render('admin/messenger/index.html.twig', [
            'jobs' => $jobs,
        ]);
    }

    /**
     * Renvoi une tâche.
     *
     * @Route("/retry/{id}", name="retry", requirements={"id"="\d+"})
     */
    public function retry(int $id, FailedJobsService $failedJobsService): RedirectResponse
    {
        $failedJobsService->retryJob($id);
        $this->addFlash('success', "La tâche numéro \"$id\" à bien été relancée");

        return $this->redirectToRoute('admin_messenger_index');
    }

    /**
     * Suppression d'une tâche.
     *
     * @Route("/delete/{id}", name="delete", requirements={"id"="\d+"})
     */
    public function delete(int $id, FailedJobsService $failedJobsService): RedirectResponse
    {
        $failedJobsService->deleteJob($id);
        $this->addFlash('success', "La tâche numéro \"$id\" à bien été supprimée");

        return $this->redirectToRoute('admin_messenger_index');
    }
}
