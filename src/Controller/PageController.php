<?php

namespace App\Controller;

use App\Entity\Page;
use App\Service\RedirectPermanentlyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Gestion des pages hors tutoriels.
 */
class PageController extends AbstractController
{
    private RedirectPermanentlyService $redirectPermanentlyService;

    public function __construct(RedirectPermanentlyService $redirectPermanentlyService)
    {
        $this->redirectPermanentlyService = $redirectPermanentlyService;
    }

    /**
     * @Route("/page/{slug}", name="pages")
     */
    public function pages(string $slug): Response
    {
        // Vérifie si une redirection permanente est définie pour cette page.
        $urlRedirect = $this->redirectPermanentlyService->redirect('page', $slug);
        if (null !== $urlRedirect) {
            return $this->redirect($urlRedirect, Response::HTTP_MOVED_PERMANENTLY);
        }

        // Récupère la page.
        $page = $this->getDoctrine()->getRepository(Page::class)->findOneBy(['slug' => $slug]);
        if (null === $page) {
            throw $this->createNotFoundException();
        }

        return $this->render('page/index.html.twig', [
            'page' => $page,
        ]);
    }
}
