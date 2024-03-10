<?php

namespace Wallabag\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Wallabag\Helper\EntityTimestampsTrait;

/**
 * SiteCredential.
 *
 * @ORM\Entity(repositoryClass="Wallabag\Repository\SiteCredentialRepository")
 * @ORM\Table(name="`site_credential`")
 * @ORM\HasLifecycleCallbacks()
 */
class SiteCredential
{
    use EntityTimestampsTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @Assert\Length(max=255)
     * @ORM\Column(name="host", type="string", length=255)
     */
    private $host;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="username", type="text")
     */
    private $username;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="password", type="text")
     */
    private $password;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Wallabag\Entity\User", inversedBy="siteCredentials")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /*
     * @param User     $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set host.
     *
     * @param string $host
     *
     * @return SiteCredential
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get host.
     *
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set username.
     *
     * @param string $username
     *
     * @return SiteCredential
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return SiteCredential
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return SiteCredential
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
