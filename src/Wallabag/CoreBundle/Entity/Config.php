<?php

namespace Wallabag\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Wallabag\UserBundle\Entity\User;

/**
 * Config.
 *
 * @ORM\Entity(repositoryClass="Wallabag\CoreBundle\Repository\ConfigRepository")
 * @ORM\Table(
 *     name="`config`",
 *     indexes={
 *         @ORM\Index(name="config_feed_token", columns={"feed_token"}, options={"lengths"={255}}),
 *     }
 * )
 */
class Config
{
    const REDIRECT_TO_HOMEPAGE = 0;
    const REDIRECT_TO_CURRENT_PAGE = 1;

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
     * @ORM\Column(name="theme", type="string", nullable=false)
     */
    private $theme;

    /**
     * @var int
     *
     * @Assert\NotBlank()
     * @Assert\Range(
     *      min = 1,
     *      max = 100000,
     *      maxMessage = "validator.item_per_page_too_high"
     * )
     * @ORM\Column(name="items_per_page", type="integer", nullable=false)
     */
    private $itemsPerPage;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="language", type="string", nullable=false)
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="feed_token", type="string", nullable=true)
     */
    private $feedToken;

    /**
     * @var int
     *
     * @ORM\Column(name="feed_limit", type="integer", nullable=true)
     * @Assert\Range(
     *      min = 1,
     *      max = 100000,
     *      maxMessage = "validator.feed_limit_too_high"
     * )
     */
    private $feedLimit;

    /**
     * If true, will return source article as <link rel="alternate"> in ATOM feed.
     *
     * @var bool
     *
     * @ORM\Column(name="feed_use_source", type="boolean", nullable=false)
     */
    private $feedUseSource = false;

    /**
     * @var float
     *
     * @ORM\Column(name="reading_speed", type="float", nullable=true)
     */
    private $readingSpeed;

    /**
     * @var string
     *
     * @ORM\Column(name="pocket_consumer_key", type="string", nullable=true)
     */
    private $pocketConsumerKey;

    /**
     * @var int
     *
     * @ORM\Column(name="action_mark_as_read", type="integer", nullable=true, options={"default" = 0})
     */
    private $actionMarkAsRead;

    /**
     * @var int
     *
     * @ORM\Column(name="list_mode", type="integer", nullable=true)
     */
    private $listMode;

    /**
     * @ORM\OneToOne(targetEntity="Wallabag\UserBundle\Entity\User", inversedBy="config")
     */
    private $user;

    /**
     * @ORM\OneToMany(targetEntity="Wallabag\CoreBundle\Entity\TaggingRule", mappedBy="config", cascade={"remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $taggingRules;

    /**
     * @ORM\OneToMany(targetEntity="Wallabag\CoreBundle\Entity\IgnoreOriginUserRule", mappedBy="config", cascade={"remove"})
     * @ORM\OrderBy({"id" = "ASC"})
     */
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
     * Set theme.
     *
     * @param string $theme
     *
     * @return Config
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Get theme.
     *
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
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
     * @param User $user
     *
     * @return Config
     */
    public function setUser(User $user = null)
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
     * @param string $feedToken
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
     * @return string
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

    public function isFeedUseSource(): bool
    {
        return $this->feedUseSource;
    }

    public function setFeedUseSource(bool $feedUseSource): void
    {
        $this->feedUseSource = $feedUseSource;
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
