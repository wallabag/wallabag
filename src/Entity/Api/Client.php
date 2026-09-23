<?php

namespace Wallabag\Entity\Api;

use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use OpenApi\Annotations as OA;
use Wallabag\Entity\User;
use Wallabag\Repository\Api\ClientRepository;

#[ORM\Table('oauth2_clients')]
#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client extends BaseClient
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    /**
     * @var string
     *
     * @OA\Property(
     *      description="Name of the API client",
     *      type="string",
     *      example="Default Client",
     * )
     */
    #[ORM\Column(name: 'name', type: 'text', nullable: false)]
    #[Groups(['user_api_with_client'])]
    protected $name;

    #[ORM\OneToMany(targetEntity: RefreshToken::class, mappedBy: 'client', cascade: ['remove'])]
    protected $refreshTokens;

    #[ORM\OneToMany(targetEntity: AccessToken::class, mappedBy: 'client', cascade: ['remove'])]
    protected $accessTokens;

    /**
     * @var string
     *
     * @OA\Property(
     *      description="Client secret used for authorization",
     *      type="string",
     *      example="2lmubx2m9vy80ss8c4wwcsg8ok44s88ocwcc8wo0w884oc8440",
     * )
     */
    #[SerializedName('client_secret')]
    #[Groups(['user_api_with_client'])]
    protected $secret;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'clients')]
    private $user;

    /**
     * Whether this is a public client (mobile app, SPA) that cannot securely store credentials.
     * Public clients MUST use PKCE for authorization code flow.
     */
    #[ORM\Column(name: 'is_public', type: 'boolean', options: ['default' => false])]
    private bool $isPublic = false;

    /**
     * Whether this client requires PKCE for authorization code flow.
     * This should be true for public clients and can be optionally enabled for confidential clients.
     */
    #[ORM\Column(name: 'require_pkce', type: 'boolean', options: ['default' => false])]
    private bool $requirePkce = false;

    public function __construct(?User $user = null)
    {
        parent::__construct();
        if (null !== $user) {
            $this->user = $user;
        }
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Client
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @OA\Property(
     *      description="Client secret used for authorization",
     *      type="string",
     *      example="3_1lpybsn0od40css4w4ko8gsc8cwwskggs8kgg448ko0owo4c84",
     * )
     */
    #[VirtualProperty]
    #[SerializedName('client_id')]
    #[Groups(['user_api_with_client'])]
    public function getClientId()
    {
        return $this->getId() . '_' . $this->getRandomId();
    }

    /**
     * Check if this is a public client (mobile app, SPA, etc.).
     *
     * Public clients cannot securely store client secrets and must use PKCE.
     *
     * @return bool True if this is a public client
     */
    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    /**
     * Set whether this client is public or confidential.
     *
     * Public clients are automatically required to use PKCE for security.
     *
     * @param bool $isPublic True for public clients (mobile, SPA), false for confidential
     */
    public function setIsPublic(bool $isPublic): self
    {
        $this->isPublic = $isPublic;

        // Public clients should always require PKCE for security
        if ($isPublic) {
            $this->requirePkce = true;
        }

        return $this;
    }

    /**
     * Check if this client requires PKCE for authorization code flow.
     *
     * @return bool True if PKCE is required for this client
     */
    public function requiresPkce(): bool
    {
        return $this->requirePkce;
    }

    /**
     * Set whether this client requires PKCE for authorization code flow.
     *
     * @param bool $requirePkce True to require PKCE, false to make it optional
     */
    public function setRequirePkce(bool $requirePkce): self
    {
        $this->requirePkce = $requirePkce;

        return $this;
    }

    /**
     * Override the checkSecret method to allow public clients without secrets.
     *
     * Public clients should not have or use client secrets according to OAuth 2.1.
     * This method returns true for public clients regardless of the secret provided.
     *
     * @param string $secret The client secret to validate
     *
     * @return bool True if secret is valid or client is public
     */
    public function checkSecret($secret): bool
    {
        if ($this->isPublic()) {
            // Public clients should not use secrets
            return true;
        }

        return parent::checkSecret($secret);
    }

    /**
     * Check if this client is allowed to use a specific grant type.
     *
     * Public clients are restricted from using the password grant for security reasons.
     *
     * @param string $grant The grant type to check (e.g., 'authorization_code', 'password')
     *
     * @return bool True if the grant type is supported by this client
     */
    public function isGrantSupported(string $grant): bool
    {
        if ($this->isPublic() && 'password' === $grant) {
            // Public clients should not use password grant for security reasons
            return false;
        }

        return \in_array($grant, $this->getAllowedGrantTypes(), true);
    }
}
