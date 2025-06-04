<?php

namespace Wallabag\Controller\Api;

use Hateoas\Configuration\Route as HateoasRoute;
use Hateoas\Representation\Factory\PagerfantaFactory;
use OpenApi\Attributes as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\Entity\EntryDeletion;
use Wallabag\Helper\EntryDeletionExpirationConfig;
use Wallabag\OpenApi\Attribute as WOA;
use Wallabag\Repository\EntryDeletionRepository;

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
            new WOA\PagerFanta\PerPageParameter(default: 100),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Returned when successful.',
                content: new WOA\PagerFanta\JsonContent(EntryDeletion::class)
            ),
            new OA\Response(
                response: 410,
                description: 'Returned when the since date is before the cutoff date.',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        'message' => new OA\Property(type: 'string'),
                    ]
                ),
                headers: [
                    new OA\Header(
                        header: 'X-Wallabag-Entry-Deletion-Cutoff',
                        description: 'The furthest cutoff timestamp possible for entry deletions.',
                        schema: new OA\Schema(type: 'integer')
                    ),
                ]
            ),
        ]
    )]
    #[IsGranted('LIST_ENTRIES')]
    public function getEntryDeletionsAction(
        Request $request,
        EntryDeletionRepository $entryDeletionRepository,
        EntryDeletionExpirationConfig $expirationConfig,
    ) {
        $this->validateAuthentication();
        $userId = $this->getUser()->getId();

        $page = $request->query->getInt('page', 1);
        $perPage = $request->query->getInt('perPage', 100);
        $order = strtolower($request->query->get('order', 'desc'));
        $since = $request->query->getInt('since', 0);

        if (!\in_array($order, ['asc', 'desc'], true)) {
            $order = 'desc';
        }

        if (0 < $since) {
            $cutoff = $expirationConfig->getCutoffDate()->getTimestamp();
            if ($since < $cutoff) {
                // Using a JSON response rather than a GoneHttpException to preserve the error message in production
                return $this->json(
                    [
                        'message' => "The requested since date ({$since}) is before the data retention cutoff date ({$cutoff}).\n"
                            . 'You can get the cutoff date programmatically from the X-Wallabag-Entry-Deletion-Cutoff header.',
                    ],
                    410,
                    headers: [
                        'X-Wallabag-Entry-Deletion-Cutoff' => $cutoff,
                    ]
                );
            }
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
