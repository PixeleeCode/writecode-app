<?php

namespace App\Service;

use App\Entity\Course;
use App\Entity\Page;
use App\Entity\RedirectPermanently;
use App\Entity\Technologie;
use App\Entity\Training;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RedirectPermanentlyService
{
    private EntityManagerInterface $em;
    private UrlGeneratorInterface $router;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $router)
    {
        $this->em = $entityManager;
        $this->router = $router;
    }

    /**
     * Insère une nouvelle redirection permanente.
     *
     * @throws ORMException
     */
    public function save(string $type, string $oldSlug, string $newSlug, int $id): bool
    {
        // Effectue une recherche de redirection permanente pour l'ancien slug
        $redirect = $this->em->getRepository(RedirectPermanently::class)->findOneBy(['new_slug' => $oldSlug]);
        if (null === $redirect) {
            $redirect = new RedirectPermanently();
        }

        $redirect->setType($type);
        $redirect->setOldSlug($oldSlug);
        $redirect->setNewSlug($newSlug);
        $redirect->setCreatedAt(new DateTimeImmutable());

        switch ($type) {
            case 'course':
                $redirect->setCourse($this->em->getReference(Course::class, $id));
                break;
            case 'training':
                $redirect->setTraining($this->em->getReference(Training::class, $id));
                break;
            case 'technology':
                $redirect->setTechnology($this->em->getReference(Technologie::class, $id));
                break;
            case 'page':
                $redirect->setPage($this->em->getReference(Page::class, $id));
                break;
        }

        $this->em->persist($redirect);
        $this->em->flush();

        return true;
    }

    /**
     * Redirige une page vers une autre.
     */
    public function redirect(string $type, string $slug): ?string
    {
        $url = null;
        $redirect = $this->em->getRepository(RedirectPermanently::class)->findOneBy([
            'type' => $type,
            'old_slug' => $slug,
        ]);

        if ($redirect) {
            switch ($type) {
                case 'course':
                    $url = $this->router->generate('course_show', ['slug' => $redirect->getNewSlug()], UrlGeneratorInterface::ABSOLUTE_PATH);
                    break;
                case 'training':
                    $url = $this->router->generate('trainings_show', ['slug' => $redirect->getNewSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
                    break;
                case 'technology':
                    $url = $this->router->generate('course_technologie', ['slug' => $redirect->getNewSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
                    break;
                case 'page':
                    $url = $this->router->generate('pages', ['slug' => $redirect->getNewSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
                    break;
            }
        }

        return $url;
    }

    /**
     * Supprime une redirection permanente.
     */
    public function delete(RedirectPermanently $redirectPermanently): bool
    {
        $this->em->remove($redirectPermanently);
        $this->em->flush();

        return true;
    }
}
