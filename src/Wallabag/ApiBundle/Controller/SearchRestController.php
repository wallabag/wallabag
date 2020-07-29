<?php

namespace Wallabag\ApiBundle\Controller;

use Hateoas\Configuration\Route;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Pagerfanta\Doctrine\ORM\QueryAdapter as DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SearchRestController extends WallabagRestController
{
    /**
     * Search all entries by term.
     *
     * @ApiDoc(
     *       parameters={
     *          {"name"="term", "dataType"="string", "required"=false, "format"="any", "description"="Any query term"},
     *          {"name"="page", "dataType"="integer", "required"=false, "format"="default '1'", "description"="what page you want."},
     *          {"name"="perPage", "dataType"="integer", "required"=false, "format"="default'30'", "description"="results per page."}
     *       }
     * )
     *
     * @return JsonResponse
     */
    public function getSearchAction(Request $request)
    {
        $this->validateAuthentication();

        $term = $request->query->get('term');
        $page = (int) $request->query->get('page', 1);
        $perPage = (int) $request->query->get('perPage', 30);

        $qb = $this->get('wallabag_core.entry_repository')
            ->getBuilderForSearchByUser(
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
            new Route(
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
