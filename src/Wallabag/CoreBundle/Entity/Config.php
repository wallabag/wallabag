<?php

namespace Wallabag\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Wallabag\UserBundle\Entity\User;

/**
 * config.
 *
 * @ORM\Entity(repositoryClass="Wallabag\CoreBundle\Repository\ConfigRepository")
 * @ORM\Table(name="`config`")
 * @ORM\Entity
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
     * @ORM\Column(name="rss_token", type="string", nullable=true)
     */
    private $rssToken;

    /**
     * @var int
     *
     * @ORM\Column(name="rss_limit", type="integer", nullable=true)
     * @Assert\Range(
     *      min = 1,
     *      max = 100000,
     *      maxMessage = "validator.rss_limit_too_high"
     * )
     */
    private $rssLimit;

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

    /*
     * @param User     $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->taggingRules = new ArrayCollection();
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
     * Set rssToken.
     *
     * @param string $rssToken
     *
     * @return Config
     */
    public function setRssToken($rssToken)
    {
        $this->rssToken = $rssToken;

        return $this;
    }

    /**
     * Get rssToken.
     *
     * @return string
     */
    public function getRssToken()
    {
        return $this->rssToken;
    }

    /**
     * Set rssLimit.
     *
     * @param int $rssLimit
     *
     * @return Config
     */
    public function setRssLimit($rssLimit)
    {
        $this->rssLimit = $rssLimit;

        return $this;
    }

    /**
     * Get rssLimit.
     *
     * @return int
     */
    public function getRssLimit()
    {
        return $this->rssLimit;
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
     * @param TaggingRule $rule
     *
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
}
