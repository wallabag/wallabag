<?php

namespace Wallabag\ApiBundle\Controller;

use Hateoas\Configuration\Route;
use Hateoas\Representation\Factory\PagerfantaFactory;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        );

        return $this->sendResponse($paginatedCollection);
    }

    /**
     * Shortcut to send data serialized in json.
     *
     * @param mixed $data
     *
     * @return JsonResponse
     */
    private function sendResponse($data)
    {
        // https://github.com/schmittjoh/JMSSerializerBundle/issues/293
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        $json = $this->get('jms_serializer')->serialize($data, 'json', $context);

        return (new JsonResponse())->setJson($json);
    }
}
