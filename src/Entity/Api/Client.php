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

    public function __construct(User $user)
    {
        parent::__construct();
        $this->user = $user;
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
}
