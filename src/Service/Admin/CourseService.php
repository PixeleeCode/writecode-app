<?php

namespace App\Service\Admin;

use App\Entity\Course;
use App\Entity\Training;
use App\Entity\User;
use App\Message\Typesense;
use App\Service\RedirectPermanentlyService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

class CourseService
{
    private EntityManagerInterface $em;
    private FileUploaderService $fileUploader;
    private NotificationService $notificationService;
    private RedirectPermanentlyService $redirectPermanentlyService;
    private ParameterBagInterface $parameter;
    private MessageBusInterface $messageBus;

    public function __construct(
        EntityManagerInterface $entityManager,
        FileUploaderService $fileUploader,
        NotificationService $notificationService,
        RedirectPermanentlyService $redirectPermanentlyService,
        ParameterBagInterface $parameterBag,
        MessageBusInterface $messageBus
    ) {
        $this->em = $entityManager;
        $this->fileUploader = $fileUploader;
        $this->notificationService = $notificationService;
        $this->redirectPermanentlyService = $redirectPermanentlyService;
        $this->parameter = $parameterBag;
        $this->messageBus = $messageBus;
    }

    /**
     * Enregistre un nouvel article.
     */
    public function save(Course $course, UploadedFile $file, User $user, bool $notifications = false): bool
    {
        $fileName = $this->fileUploader->upload($this->parameter->get('app.upload.course'), $file);

        // Ajout d'informations supplémentaires
        $course->setUser($user);
        $course->setPicture($fileName);

        // Sauvegarde
        $this->em->persist($course);
        $this->em->flush();

        // Met à jour le moteur de recherche
        if (false === $course->getDraft()) {
            $this->messageBus->dispatch(new Typesense());

            // Notifications
            if ($notifications) {
                $this->notificationService->sendNotifications(10);
            }
        }

        return true;
    }

    /**
     * Modifie un article.
     *
     * @throws ORMException
     */
    public function update(Course $course, string $oldPicture, string $oldSlug, ?UploadedFile $file = null, bool $notifications = false, bool $notifyUpdate = false): bool
    {
        $training = null;
        $course->setPicture($oldPicture);
        if ($file) {
            // Suppression de l'ancienne image
            $fileName = $this->fileUploader->upload($this->parameter->get('app.upload.course'), $file, $oldPicture);
            $course->setPicture($fileName);
        }

        // Si passage en mode brouillon, retirer de la formation dans laquelle elle se trouve
        if ($course->getDraft() && 1 === $course->getChapters()->count()) {
            // Supprime le cours comme chapitre d'une formation
            foreach ($course->getChapters() as $chapter) {
                $course->removeChapter($chapter);
                $training = $chapter->getTraining();
            }
        }

        // Sauvegarde
        $this->em->persist($course);
        $this->em->flush();

        // Si le slug à changé, on sauvegarde une redirection permanente
        if ($oldSlug !== $course->getSlug()) {
            $this->redirectPermanentlyService->save('course', $oldSlug, $course->getSlug(), $course->getId());
        }

        // Ré-ordre les chapitres en attribuant les bons numéros
        if ($training) {
            $this->reorderChapters($training);
        }

        // Met à jour le moteur de recherche
        if (false === $course->getDraft()) {
            $this->messageBus->dispatch(new Typesense());

            // Notifications
            if ($notifications) {
                if ($notifyUpdate) {
                    $this->notificationService->sendNotifications(11, $course->getTitle());
                } else {
                    $this->notificationService->sendNotifications(10);
                }
            }
        }

        return true;
    }

    /**
     * Supprime un article.
     */
    public function remove(Course $course): bool
    {
        $this->fileUploader->remove($this->parameter->get('app.upload.course'), $course->getPicture());
        $this->em->remove($course);
        $this->em->flush();

        // Met à jour le moteur de recherche
        if (false === $course->getDraft()) {
            $this->messageBus->dispatch(new Typesense());
        }

        return true;
    }

    /**
     * Ré-ordre le numéro des chapitres.
     */
    private function reorderChapters(Training $training): void
    {
        $i = 1;
        foreach ($training->getChapters() as $chapter) {
            $chapter->setPosition($i);
            $this->em->persist($chapter);
            ++$i;
        }

        $this->em->flush();
    }
}
