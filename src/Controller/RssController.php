<?php

namespace App\Controller;

use App\Repository\CourseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RssController extends AbstractController
{
    private CourseRepository $courseRepository;

    public function __construct(CourseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    /**
     * Génère un fichier RSS.
     *
     * @Route("/rss.xml", name="rss", defaults={"_format"="xml"})
     */
    public function index(): Response
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'xml');

        return $this->render('rss/rss.xml.twig', [
            'courses' => $this->courseRepository->queryAll()->setMaxResults(5)->getResult(),
        ]);
    }
}
