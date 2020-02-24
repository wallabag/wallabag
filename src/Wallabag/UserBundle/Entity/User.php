<?php

namespace Wallabag\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\XmlRoot;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface as EmailTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleTwoFactorInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Wallabag\ApiBundle\Entity\Client;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Helper\EntityTimestampsTrait;

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
class User extends BaseUser implements EmailTwoFactorInterface, GoogleTwoFactorInterface, BackupCodeInterface
{
    use EntityTimestampsTrait;

    /** @Serializer\XmlAttribute */
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"user_api", "user_api_with_client"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     *
     * @Groups({"user_api", "user_api_with_client"})
     */
    protected $name;

    /**
     * @var string
     *
     * @Groups({"user_api", "user_api_with_client"})
     */
    protected $username;

    /**
     * @var string
     *
     * @Groups({"user_api", "user_api_with_client"})
     */
    protected $email;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @Groups({"user_api", "user_api_with_client"})
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     *
     * @Groups({"user_api", "user_api_with_client"})
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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Wallabag\CoreBundle\Entity\SiteCredential", mappedBy="user", cascade={"remove"})
     */
    protected $siteCredentials;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Wallabag\ApiBundle\Entity\Client", mappedBy="user", cascade={"remove"})
     */
    protected $clients;

    /**
     * @see getFirstClient() below
     *
     * @Groups({"user_api_with_client"})
     * @Accessor(getter="getFirstClient")
     */
    protected $default_client;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $authCode;

    /**
     * @ORM\Column(name="googleAuthenticatorSecret", type="string", nullable=true)
     */
    private $googleAuthenticatorSecret;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $backupCodes;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $emailTwoFactor = false;

    public function __construct()
    {
        parent::__construct();
        $this->entries = new ArrayCollection();
        $this->roles = ['ROLE_USER'];
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
    public function isEmailTwoFactor()
    {
        return $this->emailTwoFactor;
    }

    /**
     * @param bool $emailTwoFactor
     */
    public function setEmailTwoFactor($emailTwoFactor)
    {
        $this->emailTwoFactor = $emailTwoFactor;
    }

    /**
     * Used in the user config form to be "like" the email option.
     */
    public function isGoogleTwoFactor()
    {
        return $this->isGoogleAuthenticatorEnabled();
    }

    /**
     * {@inheritdoc}
     */
    public function isEmailAuthEnabled(): bool
    {
        return $this->emailTwoFactor;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailAuthCode(): string
    {
        return $this->authCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setEmailAuthCode(string $authCode): void
    {
        $this->authCode = $authCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmailAuthRecipient(): string
    {
        return $this->email;
    }

    /**
     * {@inheritdoc}
     */
    public function isGoogleAuthenticatorEnabled(): bool
    {
        return $this->googleAuthenticatorSecret ? true : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getGoogleAuthenticatorUsername(): string
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getGoogleAuthenticatorSecret(): string
    {
        return $this->googleAuthenticatorSecret;
    }

    /**
     * {@inheritdoc}
     */
    public function setGoogleAuthenticatorSecret(?string $googleAuthenticatorSecret): void
    {
        $this->googleAuthenticatorSecret = $googleAuthenticatorSecret;
    }

    public function setBackupCodes(array $codes = null)
    {
        $this->backupCodes = $codes;
    }

    public function getBackupCodes()
    {
        return $this->backupCodes;
    }

    /**
     * {@inheritdoc}
     */
    public function isBackupCode(string $code): bool
    {
        return false === $this->findBackupCode($code) ? false : true;
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateBackupCode(string $code): void
    {
        $key = $this->findBackupCode($code);

        if (false !== $key) {
            unset($this->backupCodes[$key]);
        }
    }

    /**
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

    /**
     * Only used by the API when creating a new user it'll also return the first client (which was also created at the same time).
     *
     * @return Client
     */
    public function getFirstClient()
    {
        if (!empty($this->clients)) {
            return $this->clients->first();
        }
    }

    /**
     * Try to find a backup code from the list of backup codes of the current user.
     *
     * @param string $code Given code from the user
     *
     * @return string|false
     */
    private function findBackupCode(string $code)
    {
        foreach ($this->backupCodes as $key => $backupCode) {
            // backup code are hashed using `password_hash`
            // see ConfigController->otpAppAction
            if (password_verify($code, $backupCode)) {
                return $key;
            }
        }

        return false;
    }
}
