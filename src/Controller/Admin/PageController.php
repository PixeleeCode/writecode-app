<?php

namespace App\Controller\Admin;

use App\Entity\Page;
use App\Form\PageType;
use App\Repository\PageRepository;
use App\Service\Admin\PageService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Gestion des pages hors tutoriels.
 *
 * @IsGranted("ROLE_ADMIN")
 * @Route("/admin/pages", name="admin_page_")
 */
class PageController extends AbstractController
{
    private PageService $pageService;

    public function __construct(PageService $pageService)
    {
        $this->pageService = $pageService;
    }

    /**
     * Liste des pages.
     *
     * @Route("/", name="list")
     */
    public function index(PageRepository $pageRepository): Response
    {
        $pages = $pageRepository->findAll();

        return $this->render('admin/page/index.html.twig', [
            'pages' => $pages,
        ]);
    }

    /**
     * Ajout d'une nouvelle page.
     *
     * @Route("/new", name="new")
     */
    public function new(Request $request): Response
    {
        $page = new Page();
        $formNew = $this->createForm(PageType::class, $page, ['validation_groups' => 'page:new']);
        $formNew->handleRequest($request);

        if ($formNew->isSubmitted() && $formNew->isValid()) {
            $this->pageService->save($page);
            $this->addFlash('success', 'La page à bien été enregistrée');

            return $this->redirectToRoute('admin_page_edit', [
                'id' => $page->getId(),
            ]);
        }

        return $this->render('admin/page/new.html.twig', [
            'form' => $formNew->createView(),
        ]);
    }

    /**
     * Édition d'une page.
     *
     * @Route("/edit/{id}", name="edit", requirements={"id"="\d+"})
     */
    public function edit(Page $page, Request $request): Response
    {
        $oldSlug = $page->getSlug();

        $formEdit = $this->createForm(PageType::class, $page, ['validation_groups' => 'page:edit']);
        $formEdit->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {
            $this->pageService->update($page, $oldSlug);
            $this->addFlash('success', 'La page à bien été modifiée');
        }

        return $this->render('admin/page/edit.html.twig', [
            'page' => $page,
            'form' => $formEdit->createView(),
        ]);
    }

    /**
     * Suppression d'une page.
     *
     * @Route("/delete/{id}", name="delete", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function delete(Page $page, Request $request): RedirectResponse
    {
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('delete-page', $submittedToken)) {
            $this->pageService->remove($page);
            $this->addFlash('success', "La page \"{$page->getTitle()}\" à bien été supprimée");
        }

        return $this->redirectToRoute('admin_page_list');
    }
}
