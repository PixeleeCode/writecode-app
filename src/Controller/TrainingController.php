<?php

namespace App\Controller;

use App\Entity\Training;
use App\Repository\CourseRepository;
use App\Repository\TrainingRepository;
use App\Service\RedirectPermanentlyService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Gestion des formations côté client.
 */
class TrainingController extends AbstractController
{
    private RedirectPermanentlyService $redirectPermanentlyService;

    public function __construct(RedirectPermanentlyService $redirectPermanentlyService)
    {
        $this->redirectPermanentlyService = $redirectPermanentlyService;
    }

    /**
     * Les formations.
     *
     * @Route("/formations", name="trainings_index")
     */
    public function trainings(TrainingRepository $trainingRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $trainingRepository->queryAll();
        $page = $request->query->getInt('page', 1);
        $trainings = $paginator->paginate(
            $query,
            0 === $page ? 1 : $page,
            20
        );

        return $this->render('training/index.html.twig', [
            'trainings' => $trainings,
        ]);
    }

    /**
     * Affiche le contenu d'une formation.
     *
     * @Route("/formation/{slug<(.+)>}", name="trainings_show")
     */
    public function training(string $slug, CourseRepository $courseRepository, PaginatorInterface $paginator, Request $request): Response
    {
        // Vérifie si une redirection permanente est définie pour cette page.
        $urlRedirect = $this->redirectPermanentlyService->redirect('training', $slug);
        if (null !== $urlRedirect) {
            return $this->redirect($urlRedirect, Response::HTTP_MOVED_PERMANENTLY);
        }

        // Récupère la page.
        $training = $this->getDoctrine()->getRepository(Training::class)->findOneBy(['slug' => $slug]);
        if (null === $training) {
            throw $this->createNotFoundException();
        }

        $page = $request->query->getInt('page', 1);
        $courses = $paginator->paginate(
            $courseRepository->queryByTraining($training),
            0 === $page ? 1 : $page,
            20
        );

        return $this->render('training/show.html.twig', [
            'training' => $training,
            'courses' => $courses ?? null,
        ]);
    }
}
