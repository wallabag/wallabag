<?php

namespace Wallabag\Entity\Api;

use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Entity\RefreshToken as BaseRefreshToken;
use Wallabag\Entity\User;

#[ORM\Table('oauth2_refresh_tokens')]
#[ORM\Entity]
class RefreshToken extends BaseRefreshToken
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Client::class, inversedBy: 'refreshTokens')]
    protected $client;

    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    protected $user;
}
