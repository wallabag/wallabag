<?php

namespace Wallabag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Wallabag\Repository\FeedUrlRepository;
use Wallabag\Validator\HttpUrlWithoutCredentials;

#[ORM\Entity(repositoryClass: FeedUrlRepository::class)]
#[ORM\Table(name: '`feed_url`')]
class FeedUrl
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'feeds')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private string $title = '';

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[HttpUrlWithoutCredentials]
    private string $url = '';

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTime $updatedAt = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $automatic = false;

    /** @var Collection<int, FeedItem> */
    #[ORM\OneToMany(targetEntity: FeedItem::class, mappedBy: 'feed', cascade: ['remove'])]
    private Collection $items;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function markRefreshed(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function isAutomatic(): bool
    {
        return $this->automatic;
    }

    public function setAutomatic(bool $automatic): self
    {
        $this->automatic = $automatic;

        return $this;
    }
}
