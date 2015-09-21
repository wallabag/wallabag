<?php

namespace Wallabag\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Comment.
 *
 * @ORM\Table
 * @ORM\Entity(repositoryClass="Wallabag\CoreBundle\Repository\CommentRepository")
 */
class Comment
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
     * @ORM\Column(name="content", type="text")
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="dom", type="text")
     */
    private $dom;

    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $entry;

    /**
     * @var datetime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="comments")
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->date = new \Datetime();
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

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setDom($dom)
    {
        $this->dom = $dom;
    }

    public function getDom()
    {
        return $this->dom;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setEntry(Entry $entry)
    {
        $this->entry = $entry;
    }

    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set User.
     *
     * @param string $User
     */
    public function setUser($user)
    {
        $this->user = $user;
    }
}
