<?php

namespace Wallabag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Wallabag\Repository\FeedItemRepository;

#[ORM\Entity(repositoryClass: FeedItemRepository::class)]
#[ORM\Table(name: '`feed_item`')]
#[ORM\UniqueConstraint(columns: ['feed_id', 'url_hash'])]
class FeedItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: FeedUrl::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private FeedUrl $feed;

    #[ORM\Column(type: 'text')]
    private string $url;

    #[ORM\Column(name: 'url_hash', length: 64)]
    private string $urlHash;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $image;

    #[ORM\Column(name: 'published_at', type: 'datetime', nullable: true)]
    private ?\DateTime $publishedAt;

    #[ORM\Column(name: 'is_trashed', type: 'boolean', options: ['default' => false])]
    private bool $isTrashed = false;

    public function __construct(FeedUrl $feed, string $url, ?string $title = null, ?\DateTime $publishedAt = null, ?string $image = null)
    {
        $this->feed = $feed;
        $this->url = $url;
        $this->urlHash = hash('sha256', $url);
        $this->title = $title;
        $this->publishedAt = $publishedAt;
        $this->image = $image;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFeed(): FeedUrl
    {
        return $this->feed;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    public function dismiss(): void
    {
        $this->isTrashed = true;
        $this->title = null;
        $this->image = null;
    }

    public function isTrashed(): bool
    {
        return $this->isTrashed;
    }
}
