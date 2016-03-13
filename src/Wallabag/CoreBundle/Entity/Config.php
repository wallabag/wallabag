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
     * @ORM\Column(name="carrot", type="boolean", nullable=true)
     */
    private $carrot;

    /**
     * @var bool
     *
     * @ORM\Column(name="share_diaspora", type="boolean", nullable=true)
     */
    private $shareDiaspora;

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
     * @ORM\Column(name="share_shaarli", type="boolean", nullable=true)
     */
    private $shareShaarli;

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
     * @ORM\column(name="share_mail", type="boolean", nullable=true)
     */
    private $shareMail;

    /**
     * @var bool
     *
     * @ORM\column(name="share_twitter", type="boolean", nullable=true)
     */
    private $shareTwitter;

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
     * Set carrot.
     *
     * @param bool $carrot
     *
     * @return Config
     */
    public function setCarrot($carrot)
    {
        $this->carrot = $carrot;

        return $this;
    }

    /**
     * Get carrot.
     *
     * @return bool
     */
    public function getCarrot()
    {
        return $this->carrot;
    }

    /**
     * toggle carrot
     *
     * @return Config
     */
    public function toggleCarrot()
    {
        $this->carrot = $this->getCarrot() ^ 1;

        return $this;
    }

    /**
     * Set shareDiaspora.
     *
     * @param bool $shareDiaspora
     *
     * @return Config
     */
    public function setShareDiaspora($shareDiaspora)
    {
        $this->shareDiaspora = $shareDiaspora;

        return $this;
    }

    /**
     * Get shareDiaspora.
     *
     * @return bool
     */
    public function getShareDiaspora()
    {
        return $this->shareDiaspora;
    }

    /**
     * toggle shareDiaspora
     *
     * @return Config
     */
    public function toggleShareDiaspora()
    {
        $this->shareDiaspora = $this->getShareDiaspora() ^ 1;

        return $this;
    }

    /**
     * Set diasporaUrl.
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
     * Get diasporaUrl.
     *
     * @return string
     */
    public function getDiasporaUrl()
    {
        return $this->diasporaUrl;
    }

    /**
     * Set shareShaarli.
     *
     * @param bool $shareShaarli
     *
     * @return Config
     */
    public function setShareShaarli($shareShaarli)
    {
        $this->shareShaarli = $shareShaarli;

        return $this;
    }

    /**
     * Get shareShaarli.
     *
     * @return bool
     */
    public function getShareShaarli()
    {
        return $this->shareShaarli;
    }

    /**
     * toggle shareShaarli
     *
     * @return Config
     */
    public function toggleShareShaarli()
    {
        $this->shareShaarli = $this->getShareShaarli() ^ 1;

        return $this;
    }

    /**
     * Set shaarliUrl.
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
     * Get shaarliUrl.
     *
     * @return string
     */
    public function getShaarliUrl()
    {
        return $this->shaarliUrl;
    }

    /**
     * Set shareMail.
     *
     * @param bool $shareMail
     *
     * @return Config
     */
    public function setShareMail($shareMail)
    {
        $this->shareMail = $shareMail;

        return $this;
    }

    /**
     * Get shareMail.
     *
     * @return bool
     */
    public function getShareMail()
    {
        return $this->shareMail;
    }

    /**
     * toggle shareMail
     *
     * @return Config
     */
    public function toggleShareMail()
    {
        $this->shareMail = $this->getShareMail() ^ 1;

        return $this;
    }

    /**
     * Set shareTwitter.
     *
     * @param bool $shareTwitter
     *
     * @return Config
     */
    public function setShareTwitter($shareTwitter)
    {
        $this->shareTwitter = $shareTwitter;

        return $this;
    }

    /**
     * Get shareTwitter.
     *
     * @return bool
     */
    public function getShareTwitter()
    {
        return $this->shareTwitter;
    }

    /**
     * toggle shareTwitter
     *
     * @return Config
     */
    public function toggleShareTwitter()
    {
        $this->shareTwitter = $this->getShareTwitter() ^ 1;

        return $this;
    }

    /**
     * Set showPrintlink.
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
     * Get showPrintlink.
     *
     * @return bool
     */
    public function getShowPrintlink()
    {
        return $this->showPrintlink;
    }

    /**
     * toggle showPrintlink
     *
     * @return Config
     */
    public function toggleShowPrintlink()
    {
        $this->shareshowPrintlink = $this->getShowPrintlink() ^ 1;

        return $this;
    }
}
