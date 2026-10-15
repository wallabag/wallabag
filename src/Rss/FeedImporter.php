<?php

namespace Wallabag\Rss;

use Doctrine\ORM\EntityManagerInterface;
use Graby\Graby;
use Wallabag\Entity\Entry;
use Wallabag\Entity\RssFeed;
use Wallabag\Entity\RssSeenItem;
use Wallabag\Helper\ContentProxy;
use Wallabag\Helper\PublicHttpClient;
use Wallabag\Helper\TagsAssigner;
use Wallabag\Helper\XPostExtractor;
use Wallabag\Repository\EntryRepository;

class FeedImporter
{
    private EntityManagerInterface $em;
    private PublicHttpClient $http;
    private FeedParser $parser;
    private ContentProxy $content;
    private TagsAssigner $tags;
    private EntryRepository $entries;

    public function __construct(EntityManagerInterface $em, PublicHttpClient $http, FeedParser $parser, ContentProxy $content, TagsAssigner $tags, EntryRepository $entries)
    {
        $this->em = $em; $this->http = $http; $this->parser = $parser; $this->content = $content; $this->tags = $tags; $this->entries = $entries;
    }

    /** Claim the due time atomically, so concurrent cron runs cannot import the same feed. */
    public function run(RssFeed $feed): bool
    {
        if (!$feed->user->isEnabled()) { return false; }
        $now = new \DateTime();
        $next = (clone $now)->modify('+30 minutes');
        $meta = $this->em->getClassMetadata(RssFeed::class);
        $table = $this->em->getConnection()->getDatabasePlatform()->quoteIdentifier($meta->getTableName());
        $claimed = $this->em->getConnection()->executeUpdate('UPDATE ' . $table . ' SET next_fetch_at = ? WHERE id = ? AND enabled = ? AND (next_fetch_at IS NULL OR next_fetch_at <= ?)', [$next->format('Y-m-d H:i:s'), $feed->id, true, $now->format('Y-m-d H:i:s')], [\PDO::PARAM_STR, \PDO::PARAM_INT, \PDO::PARAM_BOOL, \PDO::PARAM_STR]);
        if (1 !== $claimed) { return false; }
        $this->em->refresh($feed);
        $errors = [];
        $deadline = microtime(true) + 120;
        try {
            $response = $this->http->get($feed->url);
            $items = $this->parser->parse($response['body'], $response['url'], $feed->maxItems);
            foreach ($items as $item) {
                if (microtime(true) > $deadline) { $errors[] = 'Import time budget reached; remaining items will retry next cycle.'; break; }
                $url = $item['url'];
                if ($this->em->getRepository(RssSeenItem::class)->findOneBy(['feed' => $feed, 'urlHash' => hash('sha256', $url)])) { continue; }
                try {
                    $this->http->validate($url);
                    $entry = $this->entries->findByUrlAndUserId($url, $feed->user->getId());
                    if (!$entry) {
                        $entry = new Entry($feed->user);
                        if (XPostExtractor::canonicalUrl($url)) {
                            $this->content->updateEntry($entry, $url);
                            if (502 === $entry->getHttpStatus()) { throw new \RuntimeException('X public capture unavailable.'); }
                        } else {
                            $article = $this->http->get($url);
                            // A fresh extractor with prefetched content cannot perform secondary or multipage requests.
                            $graby = new Graby(['singlepage' => false, 'multipage' => false]);
                            $graby->setContentAsPrefetched($article['body']);
                            $extracted = $graby->fetchContent($article['url']);
                            if (empty($extracted['html']) || empty($extracted['title']) || $extracted['html'] === $graby->getConfig('error_message')) { throw new \RuntimeException('No readable article content.'); }
                            $this->content->updateEntry($entry, $url, $extracted, true);
                        }
                        if ($item['title'] && !XPostExtractor::canonicalUrl($url)) { $entry->setTitle($item['title']); }
                        if ($item['date']) { $this->content->updatePublishedAt($entry, $item['date']); }
                        $this->tags->assignTagsToEntry($entry, array_values(array_unique(array_merge($feed->tags, array_slice(array_map(static function ($tag) { return mb_substr($tag, 0, 100); }, $item['tags']), 0, 20)))));
                        $this->em->persist($entry);
                        ++$feed->importedCount;
                    }
                    // Entry + seen marker commit together. Failed items remain retryable.
                    $this->em->persist(new RssSeenItem($feed, $url));
                    $this->em->flush();
                    // Do not dispatch the legacy image downloader: it has a separate unpinned HTTP path.
                    // Source images remain in sanitized HTML; no server-side media fetch occurs here.
                } catch (\Exception $e) {
                    $errors[] = 'Item ' . substr(hash('sha256', $url), 0, 12) . ': ' . $e->getMessage();
                }
            }
        } catch (\Exception $e) { $errors[] = $e->getMessage(); }
        $feed->lastFetchAt = $now;
        $feed->lastError = $errors ? substr(implode("\n", $errors), 0, 4000) : null;
        $this->em->flush();
        return true;
    }
}
