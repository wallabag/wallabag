<?php

namespace Wallabag\Entity\Api;

use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Entity\AuthCode as BaseAuthCode;
use Wallabag\Entity\User;

#[ORM\Table('oauth2_auth_codes')]
#[ORM\Entity]
class AuthCode extends BaseAuthCode
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Client::class)]
    protected $client;

    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: User::class)]
    protected $user;

    /**
     * PKCE code challenge.
     * Base64-URL encoded SHA256 hash of the code verifier (for S256 method)
     * or the code verifier itself (for plain method).
     */
    #[ORM\Column(name: 'code_challenge', type: 'string', length: 128, nullable: true)]
    private ?string $codeChallenge = null;

    /**
     * PKCE code challenge method.
     * Either 'S256' (recommended) or 'plain'.
     */
    #[ORM\Column(name: 'code_challenge_method', type: 'string', length: 10, nullable: true)]
    private ?string $codeChallengeMethod = null;

    /**
     * Get the PKCE code challenge value.
     *
     * @return string|null The code challenge or null if not set
     */
    public function getCodeChallenge(): ?string
    {
        return $this->codeChallenge;
    }

    /**
     * Set the PKCE code challenge value.
     *
     * @param string|null $codeChallenge The code challenge value
     */
    public function setCodeChallenge(?string $codeChallenge): self
    {
        $this->codeChallenge = $codeChallenge;

        return $this;
    }

    /**
     * Get the PKCE code challenge method.
     *
     * @return string|null The challenge method ('S256' or 'plain') or null if not set
     */
    public function getCodeChallengeMethod(): ?string
    {
        return $this->codeChallengeMethod;
    }

    /**
     * Set the PKCE code challenge method.
     *
     * @param string|null $codeChallengeMethod The challenge method ('S256' or 'plain')
     *
     * @throws \InvalidArgumentException If the method is not 'S256' or 'plain'
     */
    public function setCodeChallengeMethod(?string $codeChallengeMethod): self
    {
        // Validate that the method is one of the allowed values
        if (null !== $codeChallengeMethod && !\in_array($codeChallengeMethod, ['S256', 'plain'], true)) {
            throw new \InvalidArgumentException('Code challenge method must be either "S256" or "plain"');
        }

        $this->codeChallengeMethod = $codeChallengeMethod;

        return $this;
    }

    /**
     * Check if this authorization code has PKCE enabled.
     *
     * An authorization code has PKCE enabled if both code challenge
     * and challenge method are set.
     *
     * @return bool True if PKCE is enabled for this authorization code
     */
    public function hasPkce(): bool
    {
        return null !== $this->codeChallenge && null !== $this->codeChallengeMethod;
    }
}
