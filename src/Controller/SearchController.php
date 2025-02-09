<?php

namespace App\Controller;

use ACSEO\TypesenseBundle\Finder\CollectionFinder;
use ACSEO\TypesenseBundle\Finder\TypesenseQuery;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    private CollectionFinder $courseFinder;

    public function __construct(CollectionFinder $courseFinder)
    {
        $this->courseFinder = $courseFinder;
    }

    /**
     * Moteur de recherche.
     *
     * @Route("/recherche", name="search")
     */
    public function search(PaginatorInterface $paginator, Request $request): Response
    {
        $q = $request->query->get('q', '');

        /** @var TypesenseQuery $query */
        $query = (new TypesenseQuery($q, 'content'))
            ->addParameter('per_page', 100)
            ->filterBy('draft:false')
            ->sortBy('created_at:desc');

        $results = $this->courseFinder->query($query)->getResults();

        $page = $request->query->getInt('page', 1);
        $courses = $paginator->paginate(
            $results,
            0 === $page ? 1 : $page,
            20
        );

        return $this->render('search/index.html.twig', [
            'q' => $q,
            'total' => $courses->getTotalItemCount(),
            'courses' => $courses,
        ]);
    }
}
