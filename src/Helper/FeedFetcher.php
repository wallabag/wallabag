<?php

namespace Wallabag\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Wallabag\Entity\FeedItem;
use Wallabag\Entity\FeedUrl;
use Wallabag\Repository\FeedItemRepository;

class FeedFetcher
{
    private const ITEMS_LIMIT = 10;
    private const ITEMS_KEPT = 200;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FeedItemRepository $items,
        private readonly FeedReader $reader,
    ) {
    }

    public function fetch(FeedUrl $feed): int
    {
        $known = array_fill_keys($this->items->findKnownHashes($feed), true);
        $current = [];
        $new = [];

        foreach ($this->reader->read($feed->getUrl()) as $entry) {
            $url = $entry->getLink();
            if (!\is_string($url) || !FeedUrlValidator::isValid($url)) {
                continue;
            }

            $hash = hash('sha256', $url);
            $current[$hash] = true;
            if (\count($new) >= self::ITEMS_LIMIT || isset($known[$hash])) {
                continue;
            }

            $date = $entry->getDateCreated();
            $enclosure = (array) $entry->getEnclosure();
            $image = $enclosure['url'] ?? $enclosure['href'] ?? null;
            $new[] = new FeedItem(
                $feed,
                $url,
                $entry->getTitle(),
                null === $date ? null : \DateTime::createFromInterface($date),
                \is_string($image) && FeedUrlValidator::isValid($image) ? $image : null,
            );
            $known[$hash] = true;
        }

        foreach ($new as $item) {
            $this->entityManager->persist($item);
        }
        $this->entityManager->flush();
        $this->items->prune($feed, array_keys($current), self::ITEMS_KEPT);
        $feed->markRefreshed();
        $this->entityManager->flush();

        return \count($new);
    }
}
