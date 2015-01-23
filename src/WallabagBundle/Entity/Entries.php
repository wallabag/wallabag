<?php

namespace WallabagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entries
 *
 * @ORM\Entity(repositoryClass="WallabagBundle\Repository\EntriesRepository")
 * @ORM\Table(name="entries")
 */
class Entries
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=true)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="text", nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="url", type="text", nullable=true)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="is_read", type="decimal", precision=10, scale=0, nullable=true)
     */
    private $isRead = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="is_fav", type="decimal", precision=10, scale=0, nullable=true)
     */
    private $isFav = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="user_id", type="decimal", precision=10, scale=0, nullable=true)
     */
    private $userId;



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
     * Set title
     *
     * @param string $title
     * @return Entries
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Entries
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set isRead
     *
     * @param string $isRead
     * @return Entries
     */
    public function setIsRead($isRead)
    {
        $this->isRead = $isRead;

        return $this;
    }

    /**
     * Get isRead
     *
     * @return string 
     */
    public function getIsRead()
    {
        return $this->isRead;
    }

    public function toggleArchive()
    {
        $this->isRead = $this->getIsRead() ^ 1;
        return $this;
    }

    /**
     * Set isFav
     *
     * @param string $isFav
     * @return Entries
     */
    public function setIsFav($isFav)
    {
        $this->isFav = $isFav;

        return $this;
    }

    /**
     * Get isFav
     *
     * @return string 
     */
    public function getIsFav()
    {
        return $this->isFav;
    }

    public function toggleStar()
    {
        $this->isFav = $this->getIsFav() ^ 1;

        return $this;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Entries
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set userId
     *
     * @param string $userId
     * @return Entries
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return string 
     */
    public function getUserId()
    {
        return $this->userId;
    }
}
