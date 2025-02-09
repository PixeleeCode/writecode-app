<?php

namespace App\Twig;

use League\Glide\Signatures\SignatureFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImageExtension extends AbstractExtension
{
    private UrlGeneratorInterface $router;

    private string $secret;

    public function __construct(UrlGeneratorInterface $router, string $secret)
    {
        $this->router = $router;
        $this->secret = $secret;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset_image', [$this, 'assetImage'], ['is_safe' => ['html']]),
        ];
    }

    public function assetImage(string $path, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        $parameters['fm'] = 'pjpg';

        if ('png' === substr($path, -3)) {
            $parameters['fm'] = 'png';
        }

        $parameters['s'] = SignatureFactory::create($this->secret)->generateSignature($path, $parameters);
        $parameters['path'] = ltrim($path, '/');

        return $this->router->generate('asset_image', $parameters, $referenceType);
    }
}
