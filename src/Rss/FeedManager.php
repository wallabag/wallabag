<?php

namespace Wallabag\Rss;

use Doctrine\ORM\EntityManagerInterface;
use Wallabag\Entity\RssFeed;
use Wallabag\Entity\RssSeenItem;
use Wallabag\Helper\PublicHttpClient;
use Wallabag\Entity\User;

class FeedManager
{
    private EntityManagerInterface $em;
    private PublicHttpClient $http;

    public function __construct(EntityManagerInterface $em, PublicHttpClient $http)
    {
        $this->em = $em;
        $this->http = $http;
    }

    public function list(User $user): array
    {
        return $this->em->getRepository(RssFeed::class)->findBy(['user' => $user], ['id' => 'ASC']);
    }

    public function owned(User $user, int $id): RssFeed
    {
        $feed = $this->em->getRepository(RssFeed::class)->findOneBy(['id' => $id, 'user' => $user]);
        if (!$feed) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Feed not found.');
        }

        return $feed;
    }

    public function save(User $user, array $data, ?RssFeed $feed = null): RssFeed
    {
        if ($feed && $feed->user->getId() !== $user->getId()) {
            throw new \LogicException('Wrong owner.');
        }
        $url = $feed ? $feed->url : ($data['url'] ?? '');
        if (!\is_string($url)) {
            throw new \InvalidArgumentException('URL must be a string.');
        }
        $url = PublicHttpClient::normalize($url, $url);
        if (!$feed) {
            $this->http->validate($url);
        }
        if ($feed && isset($data['url']) && $data['url'] !== $feed->url) {
            throw new \InvalidArgumentException('Create a new subscription to change its URL.');
        }
        if (!$feed && \count($this->list($user)) >= 200) {
            throw new \InvalidArgumentException('Maximum 200 subscriptions per user.');
        }
        if (!$feed && $this->em->getRepository(RssFeed::class)->findOneBy(['user' => $user, 'urlHash' => hash('sha256', $url)])) {
            throw new \InvalidArgumentException('Already subscribed to this feed.');
        }
        $name = $data['name'] ?? ($feed ? $feed->name : $url);
        $tags = $data['tags'] ?? ($feed ? $feed->tags : []);
        $max = $data['max_items'] ?? ($feed ? $feed->maxItems : 10);
        $enabled = $data['enabled'] ?? ($feed ? $feed->enabled : true);
        if (!\is_string($name) || \strlen($name) > 255 || !\is_array($tags) || \count($tags) > 20
            || !\is_int($max) || $max < 1 || $max > 100 || !\is_bool($enabled)) {
            throw new \InvalidArgumentException('Invalid feed settings (name 255 bytes, 20 tags, max_items 1–100, boolean enabled).');
        }
        foreach ($tags as $tag) {
            if (!\is_string($tag) || \strlen($tag) > 100 || false !== strpos($tag, ',')) {
                throw new \InvalidArgumentException('Tags must be strings up to 100 bytes without commas.');
            }
        }
        $feed = $feed ?: new RssFeed($user, $url);
        $feed->name = $name;
        $feed->tags = array_values(array_unique($tags));
        $feed->maxItems = $max;
        $feed->enabled = $enabled;
        $this->em->persist($feed);
        $this->em->flush();

        return $feed;
    }

    public function delete(RssFeed $feed): void
    {
        $this->em->remove($feed);
        $this->em->flush();
    }

    /** Idempotently merge exported legacy subscriptions and successful seen URLs for this owner. */
    public function import(User $user, array $data): array
    {
        $feeds = $data['feeds'] ?? null;
        $seen = $data['seen_items'] ?? [];
        if (!\is_array($feeds) || !\is_array($seen) || \count($feeds) > 200) {
            throw new \InvalidArgumentException('Expected feeds array and optional seen_items map.');
        }
        $configured = [];
        foreach ($feeds as $row) {
            if (\is_array($row) && \is_string($row['url'] ?? null)) {
                $configured[$row['url']] = true;
            }
        }
        foreach ($seen as $url => $items) {
            if (!\is_string($url) || !\is_array($items)) {
                throw new \InvalidArgumentException('Invalid seen-items map.');
            }
            if (!isset($configured[$url])) {
                $feeds[] = ['url' => $url, 'enabled' => false];
            }
        }
        if (\count($feeds) > 200) {
            throw new \InvalidArgumentException('Maximum 200 subscriptions including historical feeds.');
        }
        $this->em->beginTransaction();
        try {
            foreach ($feeds as $row) {
                if (!\is_array($row) || !\is_string($row['url'] ?? null)) {
                    throw new \InvalidArgumentException('Invalid legacy feed.');
                }
                $url = PublicHttpClient::normalize($row['url'], $row['url']);
                $feed = $this->em->getRepository(RssFeed::class)->findOneBy(['user' => $user, 'urlHash' => hash('sha256', $url)]);
                if (!$feed) {
                    $feed = $this->save($user, $row);
                }
                $queued = [];
                foreach (($seen[$row['url']] ?? []) as $item) {
                    if (!\is_array($item) || !\is_string($item['url'] ?? null)) {
                        throw new \InvalidArgumentException('Invalid seen item.');
                    }
                    $itemUrl = PublicHttpClient::normalize($url, $item['url']);
                    $hash = hash('sha256', $itemUrl);
                    $criteria = ['feed' => $feed, 'urlHash' => $hash];
                    if (!isset($queued[$hash]) && !$this->em->getRepository(RssSeenItem::class)->findOneBy($criteria)) {
                        $this->em->persist(new RssSeenItem($feed, $itemUrl));
                        $queued[$hash] = true;
                    }
                }
                $this->em->flush();
            }
            $this->em->commit();
        } catch (\Throwable $e) {
            $this->em->rollback();
            $this->em->clear();
            throw $e;
        }

        return ['feeds' => \count($feeds)];
    }
}
