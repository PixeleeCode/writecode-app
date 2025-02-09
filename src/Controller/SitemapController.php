<?php

namespace App\Controller;

use App\Repository\CourseRepository;
use App\Repository\PageRepository;
use App\Repository\TechnologieRepository;
use App\Repository\TrainingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SitemapController extends AbstractController
{
    private CourseRepository $courseRepository;
    private TechnologieRepository $technologieRepository;
    private TrainingRepository $trainingRepository;
    private PageRepository $pageRepository;

    public function __construct(
        CourseRepository $courseRepository,
        TechnologieRepository $technologieRepository,
        TrainingRepository $trainingRepository,
        PageRepository $pageRepository
    ) {
        $this->courseRepository = $courseRepository;
        $this->technologieRepository = $technologieRepository;
        $this->trainingRepository = $trainingRepository;
        $this->pageRepository = $pageRepository;
    }

    /**
     * Génération d'un sitemap.
     *
     * @Route("/sitemap.xml", name="sitemap", defaults={"_format"="xml"})
     */
    public function index(Request $request): Response
    {
        $urls = [];
        $hostname = $request->getHost();

        $urls[] = ['loc' => $this->get('router')->generate('course_index'), 'changefreq' => 'weekly', 'priority' => '1.0'];
        $urls[] = ['loc' => $this->get('router')->generate('trainings_index'), 'changefreq' => 'monthly', 'priority' => '1.0'];

        // Tutoriels
        $courses = $this->courseRepository->queryAll()->getResult();
        foreach ($courses as $course) {
            $urls[] = [
                'loc' => $this->get('router')->generate('course_show', ['slug' => $course->getSlug()]),
                'lastmod' => $course->getUpdatedAt(),
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ];
        }

        // Technologies
        $technologies = $this->technologieRepository->findAll();
        foreach ($technologies as $technology) {
            $urls[] = [
                'loc' => $this->get('router')->generate('course_technologie', ['slug' => $technology->getSlug()]),
                'changefreq' => 'weekly',
                'priority' => '0.5',
            ];
        }

        // Formations
        $trainings = $this->trainingRepository->queryAll()->getResult();
        foreach ($trainings as $training) {
            if ($training['nb_courses'] > 1) {
                $urls[] = [
                    'loc' => $this->get('router')->generate('trainings_show', ['slug' => $training['slug']]),
                    'lastmod' => $training['updated_at'],
                    'changefreq' => 'monthly',
                    'priority' => '1.0',
                ];
            }
        }

        // Pages
        $pages = $this->pageRepository->findBy(['is_visible' => true]);
        foreach ($pages as $page) {
            $urls[] = [
                'loc' => $this->get('router')->generate('pages', ['slug' => $page->getSlug()]),
                'lastmod' => $page->getUpdatedAt(),
                'changefreq' => 'monthly',
                'priority' => '0.5',
            ];
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'xml');

        return $this->render('sitemap/sitemap.xml.twig', [
            'urls' => $urls,
            'hostname' => $hostname,
        ]);
    }
}
