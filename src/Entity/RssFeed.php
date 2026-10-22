<?php

namespace Wallabag\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'rss_feed')]
#[ORM\UniqueConstraint(name: 'rss_owner_url', columns: ['user_id', 'url_hash'])]
class RssFeed
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', nullable: false, onDelete: 'CASCADE')]
    public User $user;

    #[ORM\Column(type: 'text')]
    public string $url;

    #[ORM\Column(name: 'url_hash', type: 'string', length: 64)]
    public string $urlHash;

    #[ORM\Column(type: 'string', length: 255)]
    public string $name = '';

    #[ORM\Column(type: 'json')]
    public array $tags = [];

    #[ORM\Column(name: 'max_items', type: 'integer')]
    public int $maxItems = 10;

    #[ORM\Column(type: 'boolean')]
    public bool $enabled = true;

    #[ORM\Column(name: 'next_fetch_at', type: 'datetime', nullable: true)]
    public ?\DateTimeInterface $nextFetchAt = null;

    #[ORM\Column(name: 'last_fetch_at', type: 'datetime', nullable: true)]
    public ?\DateTimeInterface $lastFetchAt = null;

    #[ORM\Column(name: 'last_error', type: 'text', nullable: true)]
    public ?string $lastError = null;

    #[ORM\Column(name: 'imported_count', type: 'integer')]
    public int $importedCount = 0;

    public function __construct(User $user, string $url)
    {
        $this->user = $user;
        $this->url = $url;
        $this->urlHash = hash('sha256', $url);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'name' => $this->name,
            'tags' => $this->tags,
            'max_items' => $this->maxItems,
            'enabled' => $this->enabled,
            'last_fetch_at' => $this->lastFetchAt?->format(DATE_ATOM),
            'next_fetch_at' => $this->nextFetchAt?->format(DATE_ATOM),
            'last_error' => $this->lastError,
            'imported_count' => $this->importedCount,
        ];
    }
}
