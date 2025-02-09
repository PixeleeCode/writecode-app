<?php

namespace App\Controller\Admin;

use App\Entity\Course;
use App\Entity\Technologie;
use App\Entity\User;
use App\Form\Admin\CourseType;
use App\Repository\CourseRepository;
use App\Repository\TechnologieRepository;
use App\Service\Admin\CourseService;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Gestion des articles en administration.
 *
 * @Route("/admin/courses", name="admin_course_")
 */
class CourseController extends AbstractController
{
    private CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    /**
     * Liste des articles.
     *
     * @IsGranted("ROLE_MODERATOR")
     * @Route("/", name="list")
     */
    public function index(CourseRepository $courseRepository, TechnologieRepository $technologieRepository, PaginatorInterface $paginator, Request $request): Response
    {
        // On filtre sur un status.
        /** @var bool $status */
        // $status = $request->query->get('status');
        $status = null;

        // On filtre sur une technologie.
        $technologySlug = $request->query->get('technology');
        /** @var Technologie $technology */
        $technology = $technologieRepository->findOneBy(['slug' => $technologySlug]);

        $query = $courseRepository->filterByStatusOrTechnology($status, $technology);

        $page = $request->query->getInt('page', 1);
        $courses = $paginator->paginate(
            $query,
            0 === $page ? 1 : $page,
            20
        );

        return $this->render('admin/course/index.html.twig', [
            'courses' => $courses,
        ]);
    }

    /**
     * Ajout d'un nouvel article.
     *
     * @IsGranted("ROLE_ADMIN")
     * @Route("/new", name="new")
     */
    public function new(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $course = new Course();

        $formNew = $this->createForm(CourseType::class, $course, ['validation_groups' => 'course:new']);
        $formNew->handleRequest($request);

        if ($formNew->isSubmitted() && $formNew->isValid()) {
            /** @var UploadedFile $file */
            $file = $formNew->get('picture')->getData();
            $notifications = $formNew->get('notifications')->getData() ?? false;
            $this->courseService->save($course, $file, $user, $notifications);
            $this->addFlash('success', 'L\'article à bien été enregistré');

            return $this->redirectToRoute('admin_course_edit', [
                'id' => $course->getId(),
            ]);
        }

        return $this->render('admin/course/new.html.twig', [
            'form' => $formNew->createView(),
        ]);
    }

    /**
     * Édition d'un article.
     *
     * @IsGranted("ROLE_MODERATOR")
     * @Route("/edit/{id}", name="edit", requirements={"id"="\d+"})
     */
    public function edit(Course $course, Request $request): Response
    {
        // Save old datas
        $oldPicture = $course->getPicture();
        $oldSlug = $course->getSlug();

        $formEdit = $this->createForm(CourseType::class, $course, ['validation_groups' => 'course:edit']);
        $formEdit->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {
            /** @var UploadedFile $file */
            $file = $formEdit->get('picture')->getData();
            $notifications = $formEdit->get('notifications')->getData() ?? false;
            $notifyUpdate = $formEdit->get('update')->getData() ?? false;
            $this->courseService->update($course, $oldPicture, $oldSlug, $file, $notifications, $notifyUpdate);
            $this->addFlash('success', 'Les modifications ont bien été enregistrées');
        }

        return $this->render('admin/course/edit.html.twig', [
            'form' => $formEdit->createView(),
            'course' => $course,
        ]);
    }

    /**
     * Suppression d'un article.
     *
     * @IsGranted("ROLE_ADMIN")
     * @Route("/delete/{id}", name="delete", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function delete(Course $course, Request $request): RedirectResponse
    {
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('delete-course', $submittedToken)) {
            $this->courseService->remove($course);
            $this->addFlash('success', "L'article \"{$course->getTitle()}\" à bien été supprimé");
        }

        return $this->redirectToRoute('admin_course_list');
    }
}
