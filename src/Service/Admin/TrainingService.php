<?php

namespace App\Service\Admin;

use App\Entity\Training;
use App\Entity\User;
use App\Service\RedirectPermanentlyService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TrainingService
{
    private EntityManagerInterface $em;
    private FileUploaderService $fileUploader;
    private NotificationService $notificationService;
    private RedirectPermanentlyService $redirectPermanentlyService;
    private ParameterBagInterface $parameter;

    public function __construct(
        EntityManagerInterface $entityManager,
        FileUploaderService $fileUploader,
        NotificationService $notificationService,
        RedirectPermanentlyService $redirectPermanentlyService,
        ParameterBagInterface $parameterBag
    ) {
        $this->em = $entityManager;
        $this->fileUploader = $fileUploader;
        $this->notificationService = $notificationService;
        $this->redirectPermanentlyService = $redirectPermanentlyService;
        $this->parameter = $parameterBag;
    }

    /**
     * Enregistre une nouvelle formation.
     */
    public function save(Training $training, UploadedFile $file, User $user, bool $notifications = false): bool
    {
        $fileName = $this->fileUploader->upload($this->parameter->get('app.upload.training'), $file);

        $training->setUser($user);
        $training->setPicture($fileName);

        $this->em->persist($training);
        $this->em->flush();

        // Notifications
        if ($notifications) {
            $this->notificationService->sendNotifications(12, $training->getName());
        }

        return true;
    }

    /**
     * Modifie une formation.
     *
     * @throws ORMException
     */
    public function update(Training $training, string $oldPicture, string $oldSlug, ?UploadedFile $file = null, bool $notifications = false): bool
    {
        $training->setPicture($oldPicture);
        if ($file) {
            // Suppression de l'ancienne image
            $fileName = $this->fileUploader->upload($this->parameter->get('app.upload.training'), $file, $oldPicture);
            $training->setPicture($fileName);
        }

        // Sauvegarde
        $this->em->persist($training);
        $this->em->flush();

        // Si le slug à changé, on sauvegarde une redirection permanente
        if ($oldSlug !== $training->getSlug()) {
            $this->redirectPermanentlyService->save('training', $oldSlug, $training->getSlug(), $training->getId());
        }

        // Notifications
        if ($notifications) {
            $this->notificationService->sendNotifications(13, $training->getName());
        }

        return true;
    }

    /**
     * Supprime une formation.
     */
    public function remove(Training $training): bool
    {
        $this->fileUploader->remove($this->parameter->get('app.upload.training'), $training->getPicture());
        $this->em->remove($training);
        $this->em->flush();

        return true;
    }
}
