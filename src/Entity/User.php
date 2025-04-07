<?php

namespace Wallabag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\XmlRoot;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface as EmailTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleTwoFactorInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Wallabag\Entity\Api\Client;
use Wallabag\Helper\EntityTimestampsTrait;
use Wallabag\Repository\UserRepository;

/**
 * User.
 */
#[ORM\Table(name: '`user`')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity('email')]
#[UniqueEntity('username')]
#[XmlRoot('user')]
class User extends BaseUser implements EmailTwoFactorInterface, GoogleTwoFactorInterface, BackupCodeInterface
{
    use EntityTimestampsTrait;

    /** @Serializer\XmlAttribute */
    /**
     * @var int
     *
     * @OA\Property(
     *      description="The unique numeric id of the user",
     *      type="int",
     *      example=12,
     * )
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['user_api', 'user_api_with_client'])]
    protected $id;

    /**
     * @var string|null
     *
     * @OA\Property(
     *      description="The personal Name of the user",
     *      type="string",
     *      example="Walla Baggger",
     * )
     */
    #[ORM\Column(name: 'name', type: 'text', nullable: true)]
    #[Groups(['user_api', 'user_api_with_client'])]
    protected $name;

    /**
     * @var string
     *
     * @OA\Property(
     *      description="The unique username of the user",
     *      type="string",
     *      example="wallabag",
     * )
     */
    #[Groups(['user_api', 'user_api_with_client'])]
    protected $username;

    /**
     * @var string
     *
     * @OA\Property(
     *      description="E-mail address of the user",
     *      type="string",
     *      example="wallabag@wallabag.io",
     * )
     */
    #[Groups(['user_api', 'user_api_with_client'])]
    protected $email;

    /**
     * @var \DateTime
     *
     * @OA\Property(
     *      description="Creation date of the user account. (In ISO 8601 format)",
     *      type="string",
     *      example="2023-06-27T19:25:44+0000",
     * )
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['user_api', 'user_api_with_client'])]
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @OA\Property(
     *      description="Update date of the user account. (In ISO 8601 format)",
     *      type="string",
     *      example="2023-06-27T19:37:30+0000",
     * )
     */
    #[ORM\Column(name: 'updated_at', type: 'datetime')]
    #[Groups(['user_api', 'user_api_with_client'])]
    protected $updatedAt;

    #[ORM\OneToMany(targetEntity: Entry::class, mappedBy: 'user', cascade: ['remove'])]
    protected $entries;

    #[ORM\OneToOne(targetEntity: Config::class, mappedBy: 'user', cascade: ['remove'])]
    protected $config;

    /**
     * @var Collection<SiteCredential>
     */
    #[ORM\OneToMany(targetEntity: SiteCredential::class, mappedBy: 'user', cascade: ['remove'])]
    protected Collection $siteCredentials;

    /**
     * @var Collection<Client>
     */
    #[ORM\OneToMany(targetEntity: Client::class, mappedBy: 'user', cascade: ['remove'])]
    protected Collection $clients;

    /**
     * @see getFirstClient() below
     *
     * @OA\Property(
     *      description="Default client created during user registration. Used for further authorization",
     *      ref=@Model(type=Client::class, groups={"user_api_with_client"})
     * )
     */
    #[Groups(['user_api_with_client'])]
    #[Accessor(getter: 'getFirstClient')]
    protected $default_client;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $authCode;

    #[ORM\Column(name: 'googleAuthenticatorSecret', type: 'string', nullable: true)]
    private $googleAuthenticatorSecret;

    /**
     * @var array
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private $backupCodes;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    private $emailTwoFactor = false;

    public function __construct()
    {
        parent::__construct();
        $this->entries = new ArrayCollection();
        $this->siteCredentials = new ArrayCollection();
        $this->clients = new ArrayCollection();
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
     * @return Collection<Entry>
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * Set config.
     *
     * @return User
     */
    public function setConfig(?Config $config = null)
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

    public function isEmailAuthEnabled(): bool
    {
        return $this->emailTwoFactor;
    }

    public function getEmailAuthCode(): string
    {
        return $this->authCode;
    }

    public function setEmailAuthCode(string $authCode): void
    {
        $this->authCode = $authCode;
    }

    public function getEmailAuthRecipient(): string
    {
        return $this->email;
    }

    public function isGoogleAuthenticatorEnabled(): bool
    {
        return $this->googleAuthenticatorSecret ? true : false;
    }

    public function getGoogleAuthenticatorUsername(): string
    {
        return $this->username;
    }

    public function getGoogleAuthenticatorSecret(): string
    {
        return $this->googleAuthenticatorSecret;
    }

    public function setGoogleAuthenticatorSecret(?string $googleAuthenticatorSecret): void
    {
        $this->googleAuthenticatorSecret = $googleAuthenticatorSecret;
    }

    public function setBackupCodes(?array $codes = null)
    {
        $this->backupCodes = $codes;
    }

    public function getBackupCodes()
    {
        return $this->backupCodes;
    }

    public function isBackupCode(string $code): bool
    {
        return false === $this->findBackupCode($code) ? false : true;
    }

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
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
        }

        return $this;
    }

    /**
     * @return Collection<Client>
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * Only used by the API when creating a new user it'll also return the first client (which was also created at the same time).
     *
     * @return Client|false
     */
    public function getFirstClient()
    {
        if (!$this->clients->isEmpty()) {
            return $this->clients->first();
        }

        return false;
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
            if (password_verify($code, (string) $backupCode)) {
                return $key;
            }
        }

        return false;
    }
}
