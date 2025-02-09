<?php

namespace App\Twig;

use ParsedownExtra;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MarkdownExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('markdown', [$this, 'markdown'], ['is_safe' => ['html']]),
        ];
    }

    public function markdown(?string $content): string
    {
        if (null === $content) {
            return '';
        }

        $content = (new ParsedownExtra())->setBreaksEnabled(true)->setSafeMode(false)->text($content);

        // On wrap les iframe avec un ratio
        $content = preg_replace(
            '/<iframe[^>]*><\/iframe>/',
            '<div class="ratio">$0</div>',
            (string) $content
        );

        // On remplace les liens youtube par un embed.
        $content = (string) preg_replace(
            '/<p><a href="(http|https):\/\/www.youtube.com\/watch\?v=([^\"]+)">[^<]*<\/a><\/p>/',
            '<div class="embed-responsive aspect-ratio-16/9"><iframe class="embed-responsive-item" src="//www.youtube-nocookie.com/embed/$2" allowfullscreen=""></iframe></div>',
            (string) $content
        );

        // On ajoute des liens sur les nombres représentant un timestamp "00:01".
        return preg_replace_callback('/((\d{2}:){1,2}\d{2}) ([^<]*)/', static function ($matches) {
            $times = array_reverse(explode(':', $matches[1]));
            $title = $matches[3];
            $timecode = (int) ($times[2] ?? 0) * 60 * 60 + (int) $times[1] * 60 + (int) $times[0];

            return "<a href=\"#t{$timecode}\">{$matches[1]}</a> $title";
        }, $content) ?: $content;
    }
}
