<?php

namespace Wallabag\Controller\Api;

use Hateoas\Configuration\Route as HateoasRoute;
use Hateoas\Representation\Factory\PagerfantaFactory;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\Entity\EntryDeletion;
use Wallabag\Repository\EntryDeletionRepository;
use Wallabag\OpenApi\Attribute as WOA;

class EntryDeletionRestController extends WallabagRestController
{
    /**
     * Retrieve all entry deletions for the current user.
     */
    #[Route(path: '/api/entry-deletions.{_format}', name: 'api_get_entry_deletions', methods: ['GET'], defaults: ['_format' => 'json'])]
    #[OA\Get(
        summary: 'Retrieve all entry deletions for the current user.',
        tags: ['EntryDeletions'],
        parameters: [
            new OA\Parameter(
                name: 'since',
                in: 'query',
                description: 'The timestamp (in seconds) since when you want entry deletions.',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new WOA\OrderParameter(),
            new WOA\PagerFanta\PageParameter(),
            new WOA\PagerFanta\PerPageParameter(default: 100)
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returned when successful',
                content: new WOA\PagerFanta\JsonContent(EntryDeletion::class)
            )
        ]
    )]
    #[IsGranted('LIST_ENTRIES')]
    public function getEntryDeletionsAction(Request $request, EntryDeletionRepository $entryDeletionRepository)
    {
        $this->validateAuthentication();
        $userId = $this->getUser()->getId();

        $page = $request->query->get('page', 1);
        $perPage = $request->query->get('perPage', 100);
        $order = $request->query->get('order', 'desc');
        $since = $request->query->get('since');

        if (!\in_array($order, ['asc', 'desc'], true)) {
            $order = 'desc';
        }

        $pager = $entryDeletionRepository->findEntryDeletions($userId, $since, $order);
        $pager->setMaxPerPage($perPage);
        $pager->setCurrentPage($page);

        $pagerfantaFactory = new PagerfantaFactory('page', 'perPage');
        $paginatedCollection = $pagerfantaFactory->createRepresentation(
            $pager,
            new HateoasRoute(
                'api_get_entry_deletions',
                [
                    'page' => $page,
                    'perPage' => $perPage,
                    'order' => $order,
                    'since' => $since,
                ],
                true
            ),
        );

        return $this->sendResponse($paginatedCollection);
    }
}
