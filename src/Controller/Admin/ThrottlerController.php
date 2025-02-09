<?php

namespace App\Controller\Admin;

use App\Entity\Throttler;
use App\Repository\ThrottlerRepository;
use App\Service\ThrottlerService;
use Doctrine\DBAL\Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Gestion des bans.
 *
 * @IsGranted("ROLE_ADMIN")
 * @Route("/admin/throttlers", name="admin_throttler_")
 */
class ThrottlerController extends AbstractController
{
    private ThrottlerService $throttlerService;

    public function __construct(ThrottlerService $throttlerService)
    {
        $this->throttlerService = $throttlerService;
    }

    /**
     * Liste des articles.
     *
     * @Route("/", name="list")
     */
    public function index(ThrottlerRepository $throttlerRepository): Response
    {
        return $this->render('admin/throttler/index.html.twig', [
            'throttlers' => $throttlerRepository->findAll(),
        ]);
    }

    /**
     * Suppression d'un throttler.
     *
     * @Route("/delete/{id}", name="delete", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function delete(Throttler $throttler, Request $request): RedirectResponse
    {
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('delete-throttler', $submittedToken)) {
            $this->throttlerService->delete($throttler);
            $this->addFlash('success', "Le throttler \"{$throttler->getAddressIp()}\" à bien été supprimé");
        }

        return $this->redirectToRoute('admin_throttler_list');
    }

    /**
     * Suppression de tous les throttlers.
     *
     * @Route("/delete/all", name="truncate", methods={"POST"})
     *
     * @throws Exception
     */
    public function truncate(Request $request): RedirectResponse
    {
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('delete-all-throttlers', $submittedToken)) {
            $this->throttlerService->truncate();
            $this->addFlash('success', 'Tous les throttlers ont bien été supprimés');
        }

        return $this->redirectToRoute('admin_throttler_list');
    }
}
