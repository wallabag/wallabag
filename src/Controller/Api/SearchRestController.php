<?php

namespace Wallabag\Controller\Api;

use Hateoas\Configuration\Route as HateoasRoute;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Nelmio\ApiDocBundle\Annotation\Operation;
use OpenApi\Annotations as OA;
use Pagerfanta\Doctrine\ORM\QueryAdapter as DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\Repository\EntryRepository;

class SearchRestController extends WallabagRestController
{
    /**
     * Search all entries by term.
     *
     * @Operation(
     *     tags={"Search"},
     *     summary="Search all entries by term.",
     *     @OA\Parameter(
     *         name="term",
     *         in="query",
     *         description="Any query term",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="what page you want.",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="results per page.",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=30
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @Route("/api/search.{_format}", methods={"GET"}, name="api_get_search", defaults={"_format": "json"})
     *
     * @return JsonResponse
     */
    public function getSearchAction(Request $request, EntryRepository $entryRepository)
    {
        $this->validateAuthentication();

        $term = $request->query->get('term');
        $page = (int) $request->query->get('page', 1);
        $perPage = (int) $request->query->get('perPage', 30);

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
