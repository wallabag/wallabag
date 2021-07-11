<?php

namespace Wallabag\ApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations\Get;
use Hateoas\Configuration\Route;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Pagerfanta\Doctrine\ORM\QueryAdapter as DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Wallabag\CoreBundle\Repository\EntryRepository;

class SearchRestController extends AbstractWallabagRestController
{
    private $entryRepository;

    public function __construct(EntryRepository $entryRepository)
    {
        $this->entryRepository = $entryRepository;
    }

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
     *
     * @Get(
     *  path="/api/search.{_format}",
     *  name="api_get_search",
     *  defaults={
     *      "_format"="json"
     *  },
     *  requirements={
     *      "_format"="json"
     *  }
     * )
     */
    public function getSearchAction(Request $request)
    {
        $this->validateAuthentication();

        $term = $request->query->get('term');
        $page = (int) $request->query->get('page', 1);
        $perPage = (int) $request->query->get('perPage', 30);

        $qb = $this->entryRepository
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
