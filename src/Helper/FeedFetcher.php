<?php

namespace Wallabag\Helper;

use Craue\ConfigBundle\Util\Config;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Feed\Reader\Entry\AbstractEntry;
use Laminas\Feed\Reader\Entry\EntryInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Wallabag\Entity\FeedItem;
use Wallabag\Entity\FeedUrl;
use Wallabag\Import\FeedsImport;
use Wallabag\Repository\FeedItemRepository;

class FeedFetcher
{
    private const ITEMS_LIMIT = 10;
    private const ITEMS_KEPT = 200;
    private const MEDIA_NAMESPACE = 'http://search.yahoo.com/mrss/';

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
        $automatic = $feed->isAutomatic();
        $validEntries = 0;

        $result = $this->reader->read($feed->getUrl());

        foreach ($result['entries'] as $entry) {
            $url = $entry->getLink();

            if (!\is_string($url) || !FeedUrlValidator::isValid($url)) {
                continue;
            }

            $hash = hash('sha256', $url);
            $current[$hash] = true;
            $limitReached = $automatic ? ++$validEntries > self::ITEMS_LIMIT : \count($new) >= self::ITEMS_LIMIT;

            if ($limitReached || isset($known[$hash])) {
                continue;
            }

            $date = $entry->getDateCreated();
            $new[] = new FeedItem(
                $feed,
                $url,
                $this->getTitle($entry),
                null === $date ? null : (new \DateTime())->setTimestamp($date->getTimestamp()),
                $this->getEnclosureImage((array) $entry->getEnclosure()) ?? $this->getMediaImage($entry),
            );
            $known[$hash] = true;
        }

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
                $this->import->importItem($feed->getUser(), $item->getUrl(), $item->getTitle(), $item->getPublishedAt(), $producer);
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

        if ($result['url'] === $feed->getUrl()) {
            $this->items->prune($feed, array_keys($current), self::ITEMS_KEPT);
        } else {
            $feed->setUrl($result['url']);
        }
        $feed->markRefreshed();
        $this->entityManager->flush();

        return \count($new);
    }

    private function getTitle(EntryInterface $entry): ?string
    {
        if ($entry->getTitle()) {
            return $entry->getTitle();
        }

        $html = preg_replace('#<br\s*/?>|</p>#i', '$0 ', (string) $entry->getDescription());
        $text = html_entity_decode(strip_tags($html), \ENT_QUOTES | \ENT_HTML5);

        return mb_strimwidth(trim(preg_replace('/\s+/u', ' ', $text)), 0, 100, '…') ?: null;
    }

    private function getMediaImage(EntryInterface $entry): ?string
    {
        if (!$entry instanceof AbstractEntry) {
            return null;
        }

        $best = null;
        $bestWidth = -1;

        foreach ($entry->getElement()->getElementsByTagNameNS(self::MEDIA_NAMESPACE, 'content') as $media) {
            $image = $this->getEnclosureImage(['url' => $media->getAttribute('url'), 'type' => $media->getAttribute('type')]);
            $rating = $media->getElementsByTagNameNS(self::MEDIA_NAMESPACE, 'rating')->item(0)?->textContent;
            $width = (int) $media->getAttribute('width');

            if (null !== $image && 'adult' !== $rating && $width > $bestWidth) {
                $best = $image;
                $bestWidth = $width;
            }
        }

        return $best;
    }

    /** @param array{url?: string, href?: string, type?: string, length?: int|string} $enclosure */
    private function getEnclosureImage(array $enclosure): ?string
    {
        $url = $enclosure['url'] ?? $enclosure['href'] ?? null;

        if (!\is_string($url) || !FeedUrlValidator::isValid($url)) {
            return null;
        }

        $type = strtolower(trim(explode(';', $enclosure['type'] ?? '', 2)[0]));

        if (str_starts_with($type, 'image/')) {
            return $url;
        }

        if ('' !== $type && 'application/octet-stream' !== $type) {
            return null;
        }

        $extension = strtolower(pathinfo(parse_url($url, \PHP_URL_PATH) ?: '', \PATHINFO_EXTENSION));

        return \in_array($extension, ['avif', 'bmp', 'gif', 'ico', 'jpeg', 'jpg', 'png', 'svg', 'tif', 'tiff', 'webp'], true) ? $url : null;
    }
}
