<?php

namespace App\Service\Admin;

use App\Entity\Page;
use App\Service\RedirectPermanentlyService;
use Doctrine\ORM\EntityManagerInterface;

class PageService
{
    private EntityManagerInterface $em;
    private RedirectPermanentlyService $redirectPermanentlyService;

    public function __construct(EntityManagerInterface $entityManager, RedirectPermanentlyService $redirectPermanentlyService)
    {
        $this->em = $entityManager;
        $this->redirectPermanentlyService = $redirectPermanentlyService;
    }

    /**
     * Enregistre une nouvelle page.
     */
    public function save(Page $page): bool
    {
        $this->em->persist($page);
        $this->em->flush();

        return true;
    }

    /**
     * Modifie une page.
     */
    public function update(Page $page, string $oldSlug): bool
    {
        // Sauvegarde
        $this->em->persist($page);
        $this->em->flush();

        // Si le slug à changé, on sauvegarde une redirection permanente
        if ($oldSlug !== $page->getSlug()) {
            $this->redirectPermanentlyService->save('page', $oldSlug, $page->getSlug(), $page->getId());
        }

        return true;
    }

    /**
     * Supprime une page.
     */
    public function remove(Page $page): bool
    {
        $this->em->remove($page);
        $this->em->flush();

        return true;
    }
}
