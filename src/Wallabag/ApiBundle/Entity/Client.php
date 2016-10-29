<?php

namespace Wallabag\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Wallabag\UserBundle\Entity\User;

/**
 * @ORM\Table("oauth2_clients")
 * @ORM\Entity
 */
class Client extends BaseClient
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    protected $name;

    /**
     * @ORM\OneToMany(targetEntity="RefreshToken", mappedBy="client", cascade={"remove"})
     */
    protected $refreshTokens;

    /**
     * @ORM\OneToMany(targetEntity="AccessToken", mappedBy="client", cascade={"remove"})
     */
    protected $accessTokens;

    /**
     * @ORM\ManyToOne(targetEntity="Wallabag\UserBundle\Entity\User", inversedBy="clients")
     */
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
}
