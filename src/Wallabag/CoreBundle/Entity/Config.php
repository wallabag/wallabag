<?php

namespace Wallabag\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Config
 *
 * @ORM\Entity(repositoryClass="Wallabag\CoreBundle\Repository\ConfigRepository")
 * @ORM\Table(name="config")
 * @ORM\Entity
 */
class Config
{
    /**
     * @var integer
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
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="items_per_page", type="integer", nullable=false)
     */
    private $items_per_page;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="language", type="string", nullable=false)
     */
    private $language;

    /**
     * @ORM\OneToOne(targetEntity="User", inversedBy="config")
     */
    private $user;

    /*
     * @param User     $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->items_per_page = 12;
        $this->language = 'en_US';
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set theme
     *
     * @param  string $theme
     * @return Config
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * Get theme
     *
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Set items_per_page
     *
     * @param  integer $itemsPerPage
     * @return Config
     */
    public function setItemsPerPage($itemsPerPage)
    {
        $this->items_per_page = $itemsPerPage;

        return $this;
    }

    /**
     * Get items_per_page
     *
     * @return integer
     */
    public function getItemsPerPage()
    {
        return $this->items_per_page;
    }

    /**
     * Set language
     *
     * @param  string $language
     * @return Config
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set user
     *
     * @param  \Wallabag\CoreBundle\Entity\User $user
     * @return Config
     */
    public function setUser(\Wallabag\CoreBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Wallabag\CoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
