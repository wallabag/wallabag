<?php

namespace Wallabag\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Model\TrustedComputerInterface;
use FOS\UserBundle\Model\User as BaseUser;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\CoreBundle\Entity\Entry;

/**
 * User.
 *
 * @ORM\Entity(repositoryClass="Wallabag\UserBundle\Repository\UserRepository")
 * @ORM\Table(name="`user`")
 * @ORM\HasLifecycleCallbacks()
 * @ExclusionPolicy("all")
 *
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 */
class User extends BaseUser implements TwoFactorInterface, TrustedComputerInterface
{
    /**
     * @var int
     *
     * @Expose
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
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
     * @var date
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var date
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity="Wallabag\CoreBundle\Entity\Entry", mappedBy="user", cascade={"remove"})
     */
    protected $entries;

    /**
     * @ORM\OneToOne(targetEntity="Wallabag\CoreBundle\Entity\Config", mappedBy="user")
     */
    protected $config;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $authCode;

    /**
     * @var bool Enabled yes/no
     * @ORM\Column(type="boolean")
     */
    private $twoFactorAuthentication = false;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $trusted;

    public function __construct()
    {
        parent::__construct();
        $this->entries = new ArrayCollection();
        $this->roles = array('ROLE_USER');
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function timestamps()
    {
        if (is_null($this->createdAt)) {
            $this->createdAt = new \DateTime();
        }

        $this->updatedAt = new \DateTime();
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param Entry $entry
     *
     * @return User
     */
    public function addEntry(Entry $entry)
    {
        $this->entries[] = $entry;

        return $this;
    }

    /**
     * @return ArrayCollection<Entry>
     */
    public function getEntries()
    {
        return $this->entries;
    }

    public function isEqualTo(UserInterface $user)
    {
        return $this->username === $user->getUsername();
    }

    /**
     * Set config.
     *
     * @param Config $config
     *
     * @return User
     */
    public function setConfig(Config $config = null)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get config.
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return bool
     */
    public function isTwoFactorAuthentication()
    {
        return $this->twoFactorAuthentication;
    }

    /**
     * @param bool $twoFactorAuthentication
     */
    public function setTwoFactorAuthentication($twoFactorAuthentication)
    {
        $this->twoFactorAuthentication = $twoFactorAuthentication;
    }

    public function isEmailAuthEnabled()
    {
        return $this->twoFactorAuthentication;
    }

    public function getEmailAuthCode()
    {
        return $this->authCode;
    }

    public function setEmailAuthCode($authCode)
    {
        $this->authCode = $authCode;
    }

    public function addTrustedComputer($token, \DateTime $validUntil)
    {
        $this->trusted[$token] = $validUntil->format('r');
    }

    public function isTrustedComputer($token)
    {
        if (isset($this->trusted[$token])) {
            $now = new \DateTime();
            $validUntil = new \DateTime($this->trusted[$token]);

            return $now < $validUntil;
        }

        return false;
    }
}
