<?php

namespace App\Controller;

use App\Repository\PriceRepository;
use App\Service\PremiumService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Gestion de l'abonnement Premium.
 *
 * @Route("/premium", name="premium_")
 */
class PremiumController extends AbstractController
{
    /**
     * Abonnements.
     *
     * @Route("/", name="index")
     */
    public function index(PriceRepository $priceRepository, PremiumService $premiumService): Response
    {
        if ($premiumService->isPremium($this->getUser())) {
            return $this->redirectToRoute('course_index');
        }

        return $this->render('premium/index.html.twig', [
            'prices' => $priceRepository->findBy(['is_visible' => true], ['amount' => 'ASC']),
        ]);
    }
}
