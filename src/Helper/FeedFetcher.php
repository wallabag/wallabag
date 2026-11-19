<?php

namespace Wallabag\Helper;

use Craue\ConfigBundle\Util\Config;
use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Wallabag\Entity\FeedItem;
use Wallabag\Entity\FeedUrl;
use Wallabag\Import\FeedsImport;
use Wallabag\Repository\FeedItemRepository;

class FeedFetcher
{
    private const ITEMS_LIMIT = 10;
    private const ITEMS_KEPT = 200;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FeedItemRepository $items,
        private readonly FeedReader $reader,
        private readonly FeedsImport $import,
        private readonly Config $config,
        private readonly ProducerInterface $rabbitMqProducer,
        private readonly ProducerInterface $redisProducer,
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

        $automatic = $feed->isAutomatic();
        $producer = null;
        if ($automatic) {
            $producer = match (true) {
                (bool) $this->config->get('import_with_rabbitmq') => $this->rabbitMqProducer,
                (bool) $this->config->get('import_with_redis') => $this->redisProducer,
                default => null,
            };
        }
        foreach ($new as $item) {
            if ($automatic) {
                $this->import->importItem($feed->getUser(), $item->getUrl(), $item->getTitle(), $producer);
                $item->dismiss();
            }
            $this->entityManager->persist($item);

            if ($automatic) {
                $this->entityManager->flush();
            }
        }
        if (!$automatic && [] !== $new) {
            $this->entityManager->flush();
        }
        $this->items->prune($feed, array_keys($current), self::ITEMS_KEPT);
        $feed->markRefreshed();
        $this->entityManager->flush();

        return \count($new);
    }
}
