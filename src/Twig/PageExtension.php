<?php

namespace App\Twig;

use App\Repository\PageRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PageExtension extends AbstractExtension
{
    private PageRepository $pageRepository;

    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('pages', [$this, 'pages']),
        ];
    }

    public function pages(): array
    {
        return $this->pageRepository->findBy(['is_visible' => true]);
    }
}
