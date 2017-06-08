<?php

namespace Wallabag\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\XmlRoot;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Model\TrustedComputerInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Wallabag\ApiBundle\Entity\Client;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\CoreBundle\Entity\Entry;

/**
 * User.
 *
 * @XmlRoot("user")
 * @ORM\Entity(repositoryClass="Wallabag\UserBundle\Repository\UserRepository")
 * @ORM\Table(name="`user`")
 * @ORM\HasLifecycleCallbacks()
 *
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 */
class User extends BaseUser implements TwoFactorInterface, TrustedComputerInterface, GoogleTwoFactorInterface, BackupCodeInterface
{
    /** @Serializer\XmlAttribute */
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"user_api"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     *
     * @Groups({"user_api"})
     */
    protected $name;

    /**
     * @var string
     *
     * @Groups({"user_api"})
     */
    protected $username;

    /**
     * @var string
     *
     * @Groups({"user_api"})
     */
    protected $email;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @Groups({"user_api"})
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     *
     * @Groups({"user_api"})
     */
    protected $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity="Wallabag\CoreBundle\Entity\Entry", mappedBy="user", cascade={"remove"})
     */
    protected $entries;

    /**
     * @ORM\OneToOne(targetEntity="Wallabag\CoreBundle\Entity\Config", mappedBy="user", cascade={"remove"})
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
     * @ORM\Column(name="googleAuthenticatorSecret", type="string", nullable=true)
     */
    private $googleAuthenticatorSecret;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $backupCodes;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $trusted;

    /**
     * @ORM\OneToMany(targetEntity="Wallabag\ApiBundle\Entity\Client", mappedBy="user", cascade={"remove"})
     */
    protected $clients;

    public function __construct()
    {
        parent::__construct();
        $this->entries = new ArrayCollection();
        $this->roles = ['ROLE_USER'];
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
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
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

    /**
     * @param Client $client
     *
     * @return User
     */
    public function addClient(Client $client)
    {
        $this->clients[] = $client;

        return $this;
    }

    /**
     * @return ArrayCollection<Entry>
     */
    public function getClients()
    {
        return $this->clients;
    }

    public function getGoogleAuthenticatorSecret()
    {
        return $this->googleAuthenticatorSecret;
    }

    public function setGoogleAuthenticatorSecret($googleAuthenticatorSecret)
    {
        $this->googleAuthenticatorSecret = $googleAuthenticatorSecret;
    }

    /**
     * Check if it is a valid backup code.
     *
     * @param string $code
     *
     * @return bool
     */
    public function isBackupCode($code)
    {
        return in_array($code, $this->backupCodes);
    }

    /**
     * Invalidate a backup code.
     *
     * @param string $code
     */
    public function invalidateBackupCode($code)
    {
        $key = array_search($code, $this->backupCodes);
        if ($key !== false) {
            unset($this->backupCodes[$key]);
        }
    }
}
