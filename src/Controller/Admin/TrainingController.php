<?php

namespace App\Controller\Admin;

use App\Entity\Training;
use App\Entity\User;
use App\Form\Admin\TrainingType;
use App\Repository\TrainingRepository;
use App\Service\Admin\TrainingService;
use Doctrine\ORM\ORMException;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Gestion des formations en administration.
 *
 * @Route("/admin/trainings", name="admin_training_")
 */
class TrainingController extends AbstractController
{
    private TrainingService $trainingService;

    public function __construct(TrainingService $trainingService)
    {
        $this->trainingService = $trainingService;
    }

    /**
     * Liste des formations.
     *
     * @IsGranted("ROLE_MODERATOR")
     * @Route("/", name="list")
     */
    public function index(TrainingRepository $trainingRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $trainings = $paginator->paginate(
            $trainingRepository->findBy([], ['created_at' => 'DESC']),
            0 === $page ? 1 : $page,
            20
        );

        return $this->render('admin/training/index.html.twig', [
            'trainings' => $trainings,
        ]);
    }

    /**
     * Ajout d'une nouvelle formation.
     *
     * @IsGranted("ROLE_ADMIN")
     * @Route("/new", name="new")
     */
    public function new(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $training = new Training();

        $formNew = $this->createForm(TrainingType::class, $training, ['validation_groups' => 'training:new']);
        $formNew->handleRequest($request);

        if ($formNew->isSubmitted() && $formNew->isValid()) {
            /** @var UploadedFile $file */
            $file = $formNew->get('picture')->getData();
            $notifications = $formNew->get('notifications')->getData() ?? false;
            $this->trainingService->save($training, $file, $user, $notifications);
            $this->addFlash('success', 'L\'article à bien été enregistré');

            return $this->redirectToRoute('admin_training_edit', [
                'id' => $training->getId(),
            ]);
        }

        return $this->render('admin/training/new.html.twig', [
            'form' => $formNew->createView(),
        ]);
    }

    /**
     * Édition d'une formation.
     *
     * @IsGranted("ROLE_MODERATOR")
     * @Route("/edit/{id}", name="edit", requirements={"id"="\d+"})
     *
     * @throws ORMException
     */
    public function edit(Training $training, Request $request): Response
    {
        $oldPicture = $training->getPicture();
        $oldSlug = $training->getSlug();

        $formEdit = $this->createForm(TrainingType::class, $training, ['validation_groups' => 'training:edit']);
        $formEdit->handleRequest($request);

        if ($formEdit->isSubmitted() && $formEdit->isValid()) {
            /** @var UploadedFile $file */
            $file = $formEdit->get('picture')->getData();
            $notifications = $formEdit->get('notifications')->getData() ?? false;
            $this->trainingService->update($training, $oldPicture, $oldSlug, $file, $notifications);
            $this->addFlash('success', 'Les modifications ont bien été enregistrées');

            return $this->redirectToRoute('admin_training_edit', [
                'id' => $training->getId(),
            ]);
        }

        return $this->render('admin/training/edit.html.twig', [
            'form' => $formEdit->createView(),
            'training' => $training,
        ]);
    }

    /**
     * Suppression d'une formation.
     *
     * @IsGranted("ROLE_ADMIN")
     * @Route("/delete/{id}", name="delete", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function delete(Training $training, Request $request): RedirectResponse
    {
        $submittedToken = $request->request->get('token');
        if ($this->isCsrfTokenValid('delete-training', $submittedToken)) {
            $this->trainingService->remove($training);
            $this->addFlash('success', "La formation \"{$training->getName()}\" à bien été supprimée");
        }

        return $this->redirectToRoute('admin_training_list');
    }
}
