<?php

namespace App\Controller\Admin;

use App\Entity\Technologie;
use App\Form\Admin\TechnologyType;
use App\Repository\TechnologieRepository;
use App\Service\Admin\CourseService;
use App\Service\Admin\TechnologyService;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Gestion des technologies.
 *
 * @Route("/admin/technologies", name="admin_technology_")
 */
class TechnologyController extends AbstractController
{
    private TechnologyService $technologyService;
    private CourseService $courseService;

    public function __construct(TechnologyService $technologyService, CourseService $courseService)
    {
        $this->technologyService = $technologyService;
        $this->courseService = $courseService;
    }

    /**
     * Liste des technologies.
     *
     * @IsGranted("ROLE_MODERATOR")
     * @Route("/", name="list")
     */
    public function index(TechnologieRepository $technologieRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $technologies = $paginator->paginate(
            $technologieRepository->findBy([], ['name' => 'ASC']),
            0 === $page ? 1 : $page,
            20
        );

        return $this->render('admin/technology/index.html.twig', [
            'technologies' => $technologies,
        ]);
    }

    /**
     * Ajout d'une nouvelle technologie.
     *
     * @IsGranted("ROLE_ADMIN")
     * @Route("/new", name="new")
     */
    public function new(Request $request): Response
    {
        $technology = new Technologie();
        $formNew = $this->createForm(TechnologyType::class, $technology, ['validation_groups' => 'technology:new']);
        $formNew->handleRequest($request);

        if ($formNew->isSubmitted() && $formNew->isValid()) {
            /** @var UploadedFile $file */
            $file = $formNew->get('picture')->getData();
            $this->technologyService->save($technology, $file);
            $this->addFlash('success', 'La technologie à bien été enregistrée');

            return $this->redirectToRoute('admin_technology_edit', [
                'id' => $technology->getId(),
            ]);
        }

        return $this->render('admin/technology/new.html.twig', [
            'form' => $formNew->createView(),
        ]);
    }

    /**
     * Édition d'une technologie.
     *
     * @IsGranted("ROLE_MODERATOR")
     * @Route("/edit/{id}", name="edit", requirements={"id"="\d+"})
     */
    public function edit(Technologie $technology, Request $request): Response
    {
        $oldPicture = $technology->getPicture();
        $oldSlug = $technology->getSlug();

        $formEdit = $this->createForm(TechnologyType::class, $technology, ['validation_groups' => 'technology:edit']);
        $formEdit->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {
            /** @var UploadedFile $file */
            $file = $formEdit->get('picture')->getData();
            $this->technologyService->update($technology, $oldPicture, $oldSlug, $file);
            $this->addFlash('success', 'Les modifications ont bien été enregistrées');
        }

        return $this->render('admin/technology/edit.html.twig', [
            'form' => $formEdit->createView(),
            'technology' => $technology,
        ]);
    }

    /**
     * Suppression d'une technologie.
     *
     * @IsGranted("ROLE_ADMIN")
     * @Route("/delete/{id}", name="delete", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function delete(Technologie $technology, Request $request): RedirectResponse
    {
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('delete-technology', $submittedToken)) {
            foreach ($technology->getCourses() as $course) {
                $course->setDraft(true);
                $this->courseService->update($course, $course->getPicture(), $course->getSlug());
            }

            $this->technologyService->remove($technology);
            $this->addFlash('success', "La technologie \"{$technology->getName()}\" à bien été supprimée");
        }

        return $this->redirectToRoute('admin_technology_list');
    }
}
