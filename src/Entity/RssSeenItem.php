<?php

namespace Wallabag\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'rss_seen_item', uniqueConstraints: [new ORM\UniqueConstraint(name: 'rss_feed_item', columns: ['feed_id', 'url_hash'])])]
class RssSeenItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\ManyToOne(targetEntity: RssFeed::class)]
    #[ORM\JoinColumn(name: 'feed_id', nullable: false, onDelete: 'CASCADE')]
    public RssFeed $feed;

    #[ORM\Column(name: 'url_hash', type: 'string', length: 64)]
    public string $urlHash;

    #[ORM\Column(type: 'text')]
    public string $url;

    public function __construct(RssFeed $feed, string $url)
    {
        $this->feed = $feed;
        $this->url = $url;
        $this->urlHash = hash('sha256', $url);
    }
}
