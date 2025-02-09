<?php

namespace App\Controller\Admin;

use App\Entity\Price;
use App\Form\Admin\PriceType;
use App\Form\Admin\PromoteType;
use App\Repository\PriceRepository;
use App\Service\Admin\Premium\PriceService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Gestion des abonnements en administration.
 *
 * @IsGranted("ROLE_ADMIN")
 * @Route("/admin/premium", name="admin_premium_")
 */
class PremiumController extends AbstractController
{
    private PriceService $priceService;

    public function __construct(PriceService $priceService)
    {
        $this->priceService = $priceService;
    }

    /**
     * @Route("/list", name="list")
     */
    public function index(PriceRepository $priceRepository): Response
    {
        return $this->render('admin/premium/index.html.twig', [
            'prices' => $priceRepository->findAll(),
        ]);
    }

    /**
     * Ajout d'un nouvel abonnement.
     *
     * @Route("/new", name="new")
     *
     * @throws ApiErrorException
     */
    public function new(Request $request): Response
    {
        $price = new Price();
        $formNew = $this->createForm(PriceType::class, $price, ['validation_groups' => 'price:new']);
        $formNew->handleRequest($request);

        if ($formNew->isSubmitted() && $formNew->isValid()) {
            $this->priceService->save($price);
            $this->addFlash('success', 'L\'abonnement à bien été enregistré');

            return $this->redirectToRoute('admin_premium_list');
        }

        return $this->render('admin/premium/new.html.twig', [
            'form' => $formNew->createView(),
        ]);
    }

    /**
     * Édition d'un abonnement.
     *
     * @Route("/update/{id}", name="update", requirements={"id"="\d+"})
     */
    public function edit(Price $price, Request $request): Response
    {
        $formEdit = $this->createForm(PromoteType::class, $price);
        $formEdit->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {
            $this->priceService->updatePromote($price);
            $this->addFlash('success', 'L\'abonnement à bien été modifié');

            return $this->redirectToRoute('admin_premium_list');
        }

        return $this->render('admin/premium/edit.html.twig', [
            'form' => $formEdit->createView(),
        ]);
    }

    /**
     * Édition d'un abonnement.
     *
     * @Route("/edit/{id}", name="edit", requirements={"id"="\d+"})
     *
     * @throws ApiErrorException
     */
    public function update(Price $price): JsonResponse
    {
        $this->priceService->update($price);

        return $this->json($price);
    }
}
