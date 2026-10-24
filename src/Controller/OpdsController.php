<?php

namespace Wallabag\Controller;

use Pagerfanta\Doctrine\ORM\QueryAdapter as DoctrineORMAdapter;
use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wallabag\Entity\User;
use Wallabag\Helper\EntriesExport;
use Wallabag\Repository\EntryRepository;

/**
 * OPDS 1.2 (Atom) catalog.
 *
 * Mirrors FeedController: token-in-URL auth via the username_feed_token_converter,
 * PUBLIC_ACCESS firewall (see app/config/security.yml `^/opds`). Read-only — no
 * mutation. Acquisition links reuse EntriesExport to produce EPUBs.
 *
 * @see FeedController
 * @see https://github.com/wallabag/wallabag/issues/1253
 */
class OpdsController extends AbstractController
{
    private const DIGEST_DEFAULT = 20;
    private const DIGEST_MAX = 50;

    private const SHELVES = ['unread', 'starred', 'archive', 'all'];

    public function __construct(
        private readonly EntryRepository $entryRepository,
        private readonly EntriesExport $entriesExport,
        private readonly int $feedLimit,
        private readonly string $version,
    ) {
    }

    /**
     * Root OPDS navigation feed: links to each shelf plus the digest acquisition.
     */
    #[Route(path: '/opds/{username}/{token}', name: 'opds_root', methods: ['GET'], defaults: ['_format' => 'xml'])]
    #[IsGranted('PUBLIC_ACCESS')]
    #[ParamConverter('user', class: User::class, converter: 'username_feed_token_converter')]
    public function rootAction(User $user)
    {
        $token = $user->getConfig()->getFeedToken();

        $shelves = [];
        foreach (self::SHELVES as $type) {
            $shelves[] = [
                'type' => $type,
                'href' => $this->generateUrl('opds_shelf', [
                    'username' => $user->getUsername(),
                    'token' => $token,
                    'type' => $type,
                ], UrlGeneratorInterface::ABSOLUTE_URL),
            ];
        }

        $digestHref = $this->generateUrl('opds_digest', [
            'username' => $user->getUsername(),
            'token' => $token,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->render('Opds/navigation.xml.twig', [
            'user' => $user->getUsername(),
            'version' => $this->version,
            'shelves' => $shelves,
            'digestHref' => $digestHref,
            'digestDefault' => self::DIGEST_DEFAULT,
        ], new Response('', 200, ['Content-Type' => 'application/atom+xml']));
    }

    /**
     * Acquisition feed for a shelf (unread/starred/archive/all), paginated.
     */
    #[Route(path: '/opds/{username}/{token}/{type}/{page}', name: 'opds_shelf', methods: ['GET'], requirements: ['type' => 'unread|starred|archive|all', 'page' => '\d+'], defaults: ['page' => 1, '_format' => 'xml'])]
    #[IsGranted('PUBLIC_ACCESS')]
    #[ParamConverter('user', class: User::class, converter: 'username_feed_token_converter')]
    public function shelfAction(User $user, string $type, int $page)
    {
        $qb = match ($type) {
            'starred' => $this->entryRepository->getBuilderForStarredByUser($user->getId()),
            'archive' => $this->entryRepository->getBuilderForArchiveByUser($user->getId()),
            'unread' => $this->entryRepository->getBuilderForUnreadByUser($user->getId()),
            'all' => $this->entryRepository->getBuilderForAllByUser($user->getId()),
            // Unreachable: the route requirement restricts {type} to the cases above.
            default => throw new NotFoundHttpException(),
        };

        $entries = new Pagerfanta(new DoctrineORMAdapter($qb->getQuery(), true, false));
        $entries->setMaxPerPage($user->getConfig()->getFeedLimit() ?: $this->feedLimit);

        $url = $this->generateUrl('opds_shelf', [
            'username' => $user->getUsername(),
            'token' => $user->getConfig()->getFeedToken(),
            'type' => $type,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        try {
            $entries->setCurrentPage($page);
        } catch (OutOfRangeCurrentPageException) {
            if ($page > 1) {
                return $this->redirect($url . '/' . $entries->getNbPages());
            }
        }

        return $this->render('Opds/acquisition.xml.twig', [
            'type' => $type,
            'url' => $url,
            'entries' => $entries,
            'user' => $user->getUsername(),
            'token' => $user->getConfig()->getFeedToken(),
            'version' => $this->version,
        ], new Response('', 200, ['Content-Type' => 'application/atom+xml']));
    }

    /**
     * Digest: the N most recent unread entries bundled into a single EPUB.
     */
    #[Route(path: '/opds/{username}/{token}/digest.epub', name: 'opds_digest', methods: ['GET'])]
    #[IsGranted('PUBLIC_ACCESS')]
    #[ParamConverter('user', class: User::class, converter: 'username_feed_token_converter')]
    public function digestAction(Request $request, User $user)
    {
        $limit = max(1, min(self::DIGEST_MAX, $request->query->getInt('limit', self::DIGEST_DEFAULT)));

        // getBuilderForUnreadByUser already sorts by createdAt DESC (newest first).
        $entries = $this->entryRepository
            ->getBuilderForUnreadByUser($user->getId())
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        if ([] === $entries) {
            throw new NotFoundHttpException('No unread entries to digest.');
        }

        return $this->entriesExport
            ->setUser($user)
            ->setEntries($entries)
            ->updateTitle('Digest')
            ->updateAuthor('unread')
            ->exportAs('epub');
    }

    /**
     * Single-entry acquisition: export one article as EPUB.
     *
     * The EXPORT voter does NOT run under PUBLIC_ACCESS, so ownership is enforced
     * here explicitly — return 404 (not 403) on mismatch to avoid leaking existence.
     */
    #[Route(path: '/opds/{username}/{token}/entries/{id}.epub', name: 'opds_entry_export', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[IsGranted('PUBLIC_ACCESS')]
    #[ParamConverter('user', class: User::class, converter: 'username_feed_token_converter')]
    public function entryExportAction(User $user, int $id)
    {
        $entry = $this->entryRepository->find($id);

        if (null === $entry || $entry->getUser()->getId() !== $user->getId()) {
            throw new NotFoundHttpException();
        }

        return $this->entriesExport
            ->setUser($user)
            ->setEntries($entry)
            ->updateTitle('entry')
            ->updateAuthor('entry')
            ->exportAs('epub');
    }
}
