<?php

namespace Wallabag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Wallabag\Validator\HttpUrlWithoutCredentials;

#[ORM\Entity]
#[ORM\Table(name: '`feed_url`')]
class FeedUrl
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private string $title = '';

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    #[HttpUrlWithoutCredentials]
    private string $url = '';

    public function __construct(User $user)
    {
        $this->user = $user;
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
}
