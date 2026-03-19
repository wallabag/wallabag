<?php

namespace Wallabag\Controller\Api;

use Hateoas\Configuration\Route as HateoasRoute;
use Hateoas\Representation\Factory\PagerfantaFactory;
use OpenApi\Attributes as OA;
use Pagerfanta\Doctrine\ORM\QueryAdapter as DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\Entity\Entry;
use Wallabag\OpenApi\Attribute as WOA;
use Wallabag\Repository\EntryRepository;

class SearchRestController extends WallabagRestController
{
    #[Route(path: '/api/search.{_format}', name: 'api_get_search', methods: ['GET'], defaults: ['_format' => 'json'])]
    #[OA\Get(
        summary: 'Search all entries by term.',
        tags: ['Search'],
        parameters: [
            new OA\Parameter(
                name: 'term',
                in: 'query',
                description: 'Any query term.',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new WOA\PagerFanta\PageParameter(),
            new WOA\PagerFanta\PerPageParameter(),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returned when successful.',
                content: new WOA\PagerFanta\JsonContent(Entry::class)
            ),
        ]
    )]
    #[IsGranted('LIST_ENTRIES')]
    public function getSearchAction(Request $request, EntryRepository $entryRepository)
    {
        $term = $request->query->get('term');
        $page = $request->query->getInt('page', 1);
        $perPage = $request->query->getInt('perPage', 30);

        $qb = $entryRepository->getBuilderForSearchByUser(
            $this->getUser()->getId(),
            $term,
            null
        );

        $pagerAdapter = new DoctrineORMAdapter($qb->getQuery(), true, false);
        $pager = new Pagerfanta($pagerAdapter);

        $pager->setMaxPerPage($perPage);
        $pager->setCurrentPage($page);

        $pagerfantaFactory = new PagerfantaFactory('page', 'perPage');
        $paginatedCollection = $pagerfantaFactory->createRepresentation(
            $pager,
            new HateoasRoute(
                'api_get_search',
                [
                    'term' => $term,
                    'page' => $page,
                    'perPage' => $perPage,
                ],
                true
            )
        );

        return $this->sendResponse($paginatedCollection);
    }
}
