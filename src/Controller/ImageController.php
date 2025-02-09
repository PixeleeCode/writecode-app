<?php

namespace App\Controller;

use InvalidArgumentException;
use League\Glide\Responses\SymfonyResponseFactory;
use League\Glide\Server;
use League\Glide\Signatures\SignatureException;
use League\Glide\Signatures\SignatureFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Utilisation de la librairie Glide PHP.
 */
class ImageController extends AbstractController
{
    /**
     * Génère une miniature d'image et son cache.
     *
     * @Route("/assets/{path<(.+)>}", name="asset_image")
     */
    public function show(string $path, string $secret, Server $server, Request $request): StreamedResponse
    {
        $parameters = $request->query->all();

        if (count($parameters) > 0) {
            try {
                SignatureFactory::create($secret)->validateRequest($path, $parameters);
            } catch (SignatureException $e) {
                throw $this->createNotFoundException('', $e);
            }
        }

        $server->setResponseFactory(new SymfonyResponseFactory($request));

        try {
            $response = $server->getImageResponse($path, $parameters);
        } catch (InvalidArgumentException $e) {
            throw $this->createNotFoundException('', $e);
        }

        return $response;
    }
}
