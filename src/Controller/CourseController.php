<?php

namespace App\Controller;

use App\Entity\Technologie;
use App\Repository\ChapterRepository;
use App\Repository\CourseRepository;
use App\Repository\TechnologieRepository;
use App\Service\RedirectPermanentlyService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Gestion du tutoriel côté client.
 */
class CourseController extends AbstractController
{
    private RedirectPermanentlyService $redirectPermanentlyService;

    public function __construct(RedirectPermanentlyService $redirectPermanentlyService)
    {
        $this->redirectPermanentlyService = $redirectPermanentlyService;
    }

    /**
     * Accueil de WriteCode.
     *
     * @Route("/", name="course_index")
     */
    public function index(CourseRepository $courseRepository, TechnologieRepository $technologieRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $courseRepository->queryAll();
        $technologies = $technologieRepository->findAll();

        // On filtre sur une technologie.
        $technologySlug = $request->query->get('technology');
        /** @var Technologie $technology */
        $technology = $technologieRepository->findOneBy(['slug' => $technologySlug]);
        if ($technology instanceof Technologie) {
            $query = $technology->getCourses()->filter(function ($item) {
                return false === $item->getDraft();
            });
        }

        $page = $request->query->getInt('page', 1);
        $courses = $paginator->paginate(
            $query,
            0 === $page ? 1 : $page,
            20
        );

        return $this->render('course/index.html.twig', [
            'courses' => $courses,
            'technologies' => $technologies,
        ]);
    }

    /**
     * Les tutoriels selon une technologie avec sa description.
     *
     * @Route("/technologie/{slug<(.+)>}", name="course_technologie")
     */
    public function technology(?string $slug, TechnologieRepository $technologieRepository, CourseRepository $courseRepository, PaginatorInterface $paginator, Request $request): Response
    {
        // Vérifie si une redirection permanente est définie pour cette page.
        $urlRedirect = $this->redirectPermanentlyService->redirect('technology', $slug);
        if (null !== $urlRedirect) {
            return $this->redirect($urlRedirect, Response::HTTP_MOVED_PERMANENTLY);
        }

        /** @var Technologie $technology */
        $technology = $technologieRepository->findOneBy(['slug' => $slug]);
        $courses = [];

        if ($technology instanceof Technologie) {
            $page = $request->query->getInt('page', 1);
            $courses = $paginator->paginate(
                $courseRepository->queryByTechnology($technology),
                0 === $page ? 1 : $page,
                20
            );
        }

        return $this->render('course/technologie.html.twig', [
            'courses' => $courses,
            'technology' => $technology,
        ]);
    }

    /**
     * Affiche un tutoriel.
     *
     * @Route("/tutoriel/{slug<(.+)>}", name="course_show")
     */
    public function show(?string $slug, CourseRepository $courseRepository, ChapterRepository $chapterRepository): Response
    {
        // Vérifie si une redirection permanente est définie pour cette page.
        $urlRedirect = $this->redirectPermanentlyService->redirect('course', $slug);
        if (null !== $urlRedirect) {
            return $this->redirect($urlRedirect, Response::HTTP_MOVED_PERMANENTLY);
        }

        $course = $courseRepository->findOneBy(['slug' => $slug, 'draft' => false]);
        if (null === $course) {
            throw $this->createNotFoundException();
        }

        $chapter = $chapterRepository->findOneBy(['course' => $course]);

        return $this->render('course/show.html.twig', [
            'course' => $course,
            'training' => $chapter ? $chapter->getTraining() : null,
        ]);
    }
}
