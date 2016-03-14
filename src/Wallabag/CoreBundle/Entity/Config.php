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
 * @ORM\Table(name="`config`")
 * @ORM\Entity
 */
class Config
{
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
     *      maxMessage = "This will certainly kill the app"
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
     *      maxMessage = "This will certainly kill the app"
     * )
     */
    private $rssLimit;

    /**
     * @var bool
     *
     * @ORM\Column(name="enable_carrot", type="boolean", nullable=true)
     */
    private $enableCarrot;

    /**
     * @var bool
     *
     * @ORM\Column(name="enable_diaspora", type="boolean", nullable=true)
     */
    private $enableDiaspora;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="diaspora_url", type="string", nullable=true)
     */
    private $diasporaUrl;

    /**
     * @var bool
     *
     * @ORM\Column(name="enable_shaarli", type="boolean", nullable=true)
     */
    private $enableShaarli;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="shaarli_url", type="string", nullable=true)
     */
    private $shaarliUrl;

    /**
     * @var bool
     *
     * @ORM\column(name="enable_mail", type="boolean", nullable=true)
     */
    private $enableMail;

    /**
     * @var bool
     *
     * @ORM\column(name="enable_twitter", type="boolean", nullable=true)
     */
    private $enableTwitter;

    /**
     * @var bool
     *
     * @ORM\column(name="show_printlink", type="boolean", nullable=true)
     */
    private $showPrintlink;

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

    /**
     * Set Enable to Carrot.
     *
     * @param bool $carrot
     *
     * @return Config
     */
    public function setEnableCarrot($enableCarrot)
    {
        $this->enableCarrot = $enableCarrot;

        return $this;
    }

    /**
     * Get Enable to Carrot.
     *
     * @return bool
     */
    public function getEnableCarrot()
    {
        return $this->enableCarrot;
    }

    /**
     * Toggle Enable to Carrot.
     *
     * @return Config
     */
    public function toggleEnableCarrot()
    {
        $this->enableCarrot = $this->getEnableCarrot() ^ 1;

        return $this;
    }

    /**
     * Set Enable to Diaspora.
     *
     * @param bool $enableDiaspora
     *
     * @return Config
     */
    public function setEnableDiaspora($enableDiaspora)
    {
        $this->enableDiaspora = $enableDiaspora;

        return $this;
    }

    /**
     * Get Enable to Diaspora.
     *
     * @return bool
     */
    public function getEnableDiaspora()
    {
        return $this->enableDiaspora;
    }

    /**
     * Toggle Enable to Diaspora.
     *
     * @return Config
     */
    public function toggleEnableDiaspora()
    {
        $this->enableDiaspora = $this->getEnableDiaspora() ^ 1;

        return $this;
    }

    /**
     * Set Diaspora Url.
     *
     * @param string $diasporaUrl
     *
     * @return Config
     */
    public function setDiasporaUrl($diasporaUrl)
    {
        $this->diasporaUrl = $diasporaUrl;

        return $this;
    }

    /**
     * Get Diaspora Url.
     *
     * @return string
     */
    public function getDiasporaUrl()
    {
        return $this->diasporaUrl;
    }

    /**
     * Set Enable to Shaarli.
     *
     * @param bool $enableShaarli
     *
     * @return Config
     */
    public function setEnableShaarli($enableShaarli)
    {
        $this->enableShaarli = $enableShaarli;

        return $this;
    }

    /**
     * Get Enable to Shaarli.
     *
     * @return bool
     */
    public function getEnableShaarli()
    {
        return $this->enableShaarli;
    }

    /**
     * Toggle Enable to Shaarli.
     *
     * @return Config
     */
    public function toggleEnableShaarli()
    {
        $this->enableShaarli = $this->getEnableShaarli() ^ 1;

        return $this;
    }

    /**
     * Set Shaarli Url.
     *
     * @param string $shaarliUrl
     *
     * @return Config
     */
    public function setShaarliUrl($shaarliUrl)
    {
        $this->shaarliUrl = $shaarliUrl;

        return $this;
    }

    /**
     * Get Shaarli Url.
     *
     * @return string
     */
    public function getShaarliUrl()
    {
        return $this->shaarliUrl;
    }

    /**
     * Set Enable to Mail.
     *
     * @param bool $enableMail
     *
     * @return Config
     */
    public function setEnableMail($enableMail)
    {
        $this->enableMail = $enableMail;

        return $this;
    }

    /**
     * Get Enable to Mail.
     *
     * @return bool
     */
    public function getEnableMail()
    {
        return $this->enableMail;
    }

    /**
     * Toggle Enable to Mail.
     *
     * @return Config
     */
    public function toggleEnableMail()
    {
        $this->enableMail = $this->getEnableMail() ^ 1;

        return $this;
    }

    /**
     * Set Enable to Twitter.
     *
     * @param bool $enableTwitter
     *
     * @return Config
     */
    public function setEnableTwitter($enableTwitter)
    {
        $this->enableTwitter = $enableTwitter;

        return $this;
    }

    /**
     * Get Enable to Twitter.
     *
     * @return bool
     */
    public function getEnableTwitter()
    {
        return $this->enableTwitter;
    }

    /**
     * Toggle Enable to Twitter.
     *
     * @return Config
     */
    public function toggleEnableTwitter()
    {
        $this->enableTwitter = $this->getEnableTwitter() ^ 1;

        return $this;
    }

    /**
     * Set Show Print Link.
     *
     * @param bool $showPrintlink
     *
     * @return Config
     */
    public function setShowPrintlink($showPrintlink)
    {
        $this->showPrintlink = $showPrintlink;

        return $this;
    }

    /**
     * Get Show Print Link.
     *
     * @return bool
     */
    public function getShowPrintlink()
    {
        return $this->showPrintlink;
    }

    /**
     * Toggle Show Print Link.
     *
     * @return Config
     */
    public function toggleShowPrintlink()
    {
        $this->showPrintlink = $this->getShowPrintlink() ^ 1;

        return $this;
    }
}
