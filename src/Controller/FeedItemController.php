<?php

namespace Wallabag\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Wallabag\Entity\FeedUrl;
use Wallabag\Repository\FeedItemRepository;

class FeedItemController extends AbstractController
{
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
}
