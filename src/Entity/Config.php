<?php

namespace Wallabag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Wallabag\Repository\ConfigRepository;

/**
 * Config.
 */
#[ORM\Table(name: '`config`')]
#[ORM\Index(columns: ['feed_token'])]
#[ORM\Entity(repositoryClass: ConfigRepository::class)]
class Config
{
    public const REDIRECT_TO_HOMEPAGE = 0;
    public const REDIRECT_TO_CURRENT_PAGE = 1;

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['config_api'])]
    private $id;

    /**
     * @var int
     */
    #[ORM\Column(name: 'items_per_page', type: 'integer', nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 100000, maxMessage: 'validator.item_per_page_too_high')]
    #[Groups(['config_api'])]
    private $itemsPerPage;

    /**
     * @var string
     */
    #[ORM\Column(name: 'language', type: 'string', nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['config_api'])]
    private $language;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'feed_token', type: 'string', nullable: true)]
    #[Groups(['config_api'])]
    private $feedToken;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'feed_limit', type: 'integer', nullable: true)]
    #[Assert\Range(min: 1, max: 100000, maxMessage: 'validator.feed_limit_too_high')]
    #[Groups(['config_api'])]
    private $feedLimit;

    /**
     * @var float|null
     */
    #[ORM\Column(name: 'reading_speed', type: 'float', nullable: true)]
    #[Groups(['config_api'])]
    private $readingSpeed;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'pocket_consumer_key', type: 'string', nullable: true)]
    private $pocketConsumerKey;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'action_mark_as_read', type: 'integer', nullable: true, options: ['default' => 0])]
    #[Groups(['config_api'])]
    private $actionMarkAsRead;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'list_mode', type: 'integer', nullable: true)]
    #[Groups(['config_api'])]
    private $listMode;

    /**
     * @var int|null
     */
    #[ORM\Column(name: 'display_thumbnails', type: 'integer', nullable: true, options: ['default' => 1])]
    #[Groups(['config_api'])]
    private $displayThumbnails;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'font', type: 'text', nullable: true)]
    #[Groups(['config_api'])]
    private $font;

    /**
     * @var float|null
     */
    #[ORM\Column(name: 'fontsize', type: 'float', nullable: true)]
    #[Groups(['config_api'])]
    private $fontsize;

    /**
     * @var float|null
     */
    #[ORM\Column(name: 'line_height', type: 'float', nullable: true)]
    #[Groups(['config_api'])]
    private $lineHeight;

    /**
     * @var float|null
     */
    #[ORM\Column(name: 'max_width', type: 'float', nullable: true)]
    #[Groups(['config_api'])]
    private $maxWidth;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'custom_css', type: 'text', nullable: true)]
    private $customCSS;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'config')]
    private $user;

    /**
     * @var ArrayCollection<TaggingRule>
     */
    #[ORM\OneToMany(targetEntity: TaggingRule::class, mappedBy: 'config', cascade: ['remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $taggingRules;

    /**
     * @var ArrayCollection<IgnoreOriginUserRule>
     */
    #[ORM\OneToMany(targetEntity: IgnoreOriginUserRule::class, mappedBy: 'config', cascade: ['remove'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private $ignoreOriginRules;

    /*
     * @param User     $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->taggingRules = new ArrayCollection();
        $this->ignoreOriginRules = new ArrayCollection();
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
     * Set itemsPerPage.
     *
     * @param int $itemsPerPage
     *
     * @return Config
     */
    public function setItemsPerPage($itemsPerPage)
    {
        $this->itemsPerPage = $itemsPerPage;

        return $this;
    }

    /**
     * Get itemsPerPage.
     *
     * @return int
     */
    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    /**
     * Set language.
     *
     * @param string $language
     *
     * @return Config
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language.
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set user.
     *
     * @return Config
     */
    public function setUser(?User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set feed Token.
     *
     * @param string|null $feedToken
     *
     * @return Config
     */
    public function setFeedToken($feedToken)
    {
        $this->feedToken = $feedToken;

        return $this;
    }

    /**
     * Get feedToken.
     *
     * @return string|null
     */
    public function getFeedToken()
    {
        return $this->feedToken;
    }

    /**
     * Set Feed Limit.
     *
     * @param int $feedLimit
     *
     * @return Config
     */
    public function setFeedLimit($feedLimit)
    {
        $this->feedLimit = $feedLimit;

        return $this;
    }

    /**
     * Get Feed Limit.
     *
     * @return int
     */
    public function getFeedLimit()
    {
        return $this->feedLimit;
    }

    /**
     * Set readingSpeed.
     *
     * @param float $readingSpeed
     *
     * @return Config
     */
    public function setReadingSpeed($readingSpeed)
    {
        $this->readingSpeed = $readingSpeed;

        return $this;
    }

    /**
     * Get readingSpeed.
     *
     * @return float
     */
    public function getReadingSpeed()
    {
        return $this->readingSpeed;
    }

    /**
     * Set pocketConsumerKey.
     *
     * @param string $pocketConsumerKey
     *
     * @return Config
     */
    public function setPocketConsumerKey($pocketConsumerKey)
    {
        $this->pocketConsumerKey = $pocketConsumerKey;

        return $this;
    }

    /**
     * Get pocketConsumerKey.
     *
     * @return string
     */
    public function getPocketConsumerKey()
    {
        return $this->pocketConsumerKey;
    }

    /**
     * @return int
     */
    public function getActionMarkAsRead()
    {
        return $this->actionMarkAsRead;
    }

    /**
     * @param int $actionMarkAsRead
     *
     * @return Config
     */
    public function setActionMarkAsRead($actionMarkAsRead)
    {
        $this->actionMarkAsRead = $actionMarkAsRead;

        return $this;
    }

    /**
     * @return int
     */
    public function getListMode()
    {
        return $this->listMode;
    }

    /**
     * @param int $listMode
     *
     * @return Config
     */
    public function setListMode($listMode)
    {
        $this->listMode = $listMode;

        return $this;
    }

    public function getDisplayThumbnails(): bool
    {
        return (bool) $this->displayThumbnails;
    }

    /**
     * @return Config
     */
    public function setDisplayThumbnails(bool $displayThumbnails)
    {
        $this->displayThumbnails = $displayThumbnails ? 1 : 0;

        return $this;
    }

    public function getFont(): ?string
    {
        return $this->font;
    }

    /**
     * @return $this
     */
    public function setFont(string $font): self
    {
        $this->font = $font;

        return $this;
    }

    public function getFontsize(): ?float
    {
        return $this->fontsize;
    }

    /**
     * @return $this
     */
    public function setFontsize(float $fontsize): self
    {
        $this->fontsize = $fontsize;

        return $this;
    }

    public function getLineHeight(): ?float
    {
        return $this->lineHeight;
    }

    /**
     * @return $this
     */
    public function setLineHeight(float $lineHeight): self
    {
        $this->lineHeight = $lineHeight;

        return $this;
    }

    public function getMaxWidth(): ?float
    {
        return $this->maxWidth;
    }

    /**
     * @return $this
     */
    public function setMaxWidth(float $maxWidth): self
    {
        $this->maxWidth = $maxWidth;

        return $this;
    }

    public function getCustomCSS(): ?string
    {
        return $this->customCSS;
    }

    /**
     * @return $this
     */
    public function setCustomCSS(?string $customCSS): self
    {
        $this->customCSS = $customCSS;

        return $this;
    }

    /**
     * @return Config
     */
    public function addTaggingRule(TaggingRule $rule)
    {
        $this->taggingRules[] = $rule;

        return $this;
    }

    /**
     * @return ArrayCollection<TaggingRule>
     */
    public function getTaggingRules()
    {
        return $this->taggingRules;
    }

    /**
     * @return Config
     */
    public function addIgnoreOriginRule(IgnoreOriginUserRule $rule)
    {
        $this->ignoreOriginRules[] = $rule;

        return $this;
    }

    /**
     * @return ArrayCollection<IgnoreOriginUserRule>
     */
    public function getIgnoreOriginRules()
    {
        return $this->ignoreOriginRules;
    }
}
