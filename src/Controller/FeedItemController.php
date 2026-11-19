<?php

namespace Wallabag\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\Entity\FeedItem;
use Wallabag\Entity\FeedUrl;
use Wallabag\Repository\FeedItemRepository;

class FeedItemController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route(path: '/atom/items/{id}', name: 'display_feed_items', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('LIST_ITEMS', subject: 'feed')]
    public function items(FeedUrl $feed, FeedItemRepository $items): Response
    {
        return $this->render('FeedSubscription/items.html.twig', [
            'feed' => $feed,
            'items' => $items->findPending($feed),
        ]);
    }

    #[Route(path: '/atom/purge/{id}', name: 'purge_feed', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('PURGE_ITEMS', subject: 'feed')]
    public function purge(Request $request, FeedUrl $feed, FeedItemRepository $items): Response
    {
        $token = $request->request->all()['token'] ?? null;
        if (!\is_string($token) || !$this->isCsrfTokenValid('feed-purge-' . $feed->getId(), $token)) {
            throw new BadRequestHttpException('Invalid CSRF token.');
        }

        $dismissed = $items->dismissPending($feed);
        $this->addFlash('notice', $dismissed > 0 ? 'feeds.flash.purged' : 'feeds.flash.no_pending_articles');

        return $this->redirectToRoute('all_import_feed');
    }

    #[Route(path: '/atom/item/delete/{id}', name: 'delete_item', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('DISMISS', subject: 'item')]
    public function dismiss(Request $request, FeedItem $item): Response
    {
        $feed = $item->getFeed();
        $token = $request->request->all()['token'] ?? null;
        if (!\is_string($token) || !$this->isCsrfTokenValid('feed-delete-item-' . $item->getId(), $token)) {
            throw new BadRequestHttpException('Invalid CSRF token.');
        }

        if ($item->isTrashed()) {
            $this->addFlash('notice', 'feeds.flash.already_processed');
        } else {
            $item->dismiss();
            $this->entityManager->flush();
            $this->addFlash('notice', 'feeds.flash.dismissed');
        }

        return $this->redirectToRoute('display_feed_items', ['id' => $feed->getId()]);
    }
}
