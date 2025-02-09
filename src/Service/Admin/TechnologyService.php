<?php

namespace App\Service\Admin;

use App\Entity\Technologie;
use App\Service\RedirectPermanentlyService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TechnologyService
{
    private EntityManagerInterface $em;
    private FileUploaderService $fileUploader;
    private RedirectPermanentlyService $redirectPermanentlyService;
    private ParameterBagInterface $parameter;

    public function __construct(EntityManagerInterface $entityManager, FileUploaderService $fileUploader, RedirectPermanentlyService $redirectPermanentlyService, ParameterBagInterface $parameterBag)
    {
        $this->em = $entityManager;
        $this->fileUploader = $fileUploader;
        $this->redirectPermanentlyService = $redirectPermanentlyService;
        $this->parameter = $parameterBag;
    }

    /**
     * Enregistre une nouvelle technologie.
     */
    public function save(Technologie $technology, UploadedFile $file): bool
    {
        $fileName = $this->fileUploader->upload($this->parameter->get('app.upload.technology'), $file);
        $technology->setPicture($fileName);

        $this->em->persist($technology);
        $this->em->flush();

        return true;
    }

    /**
     * Modifie une technologie.
     *
     * @throws ORMException
     */
    public function update(Technologie $technology, string $oldPicture, string $oldSlug, ?UploadedFile $file = null): bool
    {
        $technology->setPicture($oldPicture);
        if ($file) {
            // Suppression de l'ancienne image
            $fileName = $this->fileUploader->upload($this->parameter->get('app.upload.technology'), $file, $oldPicture);
            $technology->setPicture($fileName);
        }

        // Sauvegarde
        $this->em->persist($technology);
        $this->em->flush();

        // Si le slug à changé, on sauvegarde une redirection permanente
        if ($oldSlug !== $technology->getSlug()) {
            $this->redirectPermanentlyService->save('technology', $oldSlug, $technology->getSlug(), $technology->getId());
        }

        return true;
    }

    /**
     * Supprime une technologie.
     */
    public function remove(Technologie $technology): bool
    {
        $this->fileUploader->remove($this->parameter->get('app.upload.technology'), $technology->getPicture());
        $this->em->remove($technology);
        $this->em->flush();

        return true;
    }
}
