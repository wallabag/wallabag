<?php

namespace Wallabag\CommentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\SerializedName;
use Wallabag\UserBundle\Entity\User;
use Wallabag\CoreBundle\Entity\Entry;

/**
 * Comment.
 *
 * @ORM\Table(name="comment")
 * @ORM\Entity(repositoryClass="Wallabag\CommentBundle\Repository\CommentRepository")
 *
 * @ExclusionPolicy("none")
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
     * @ORM\Column(name="text", type="text")
     */
    private $text;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @var string
     *
     * @ORM\Column(name="quote", type="string", length=255, nullable=true)
     */
    private $quote;

    /**
     * @var array
     *
     * @ORM\Column(name="ranges", type="array", length=255, nullable=true)
     */
    private $ranges;

    /**
     * @Exclude
     *
     * @ORM\ManyToOne(targetEntity="Wallabag\UserBundle\Entity\User")
     */
    private $user;

    /**
     * @Exclude
     *
     * @ORM\ManyToOne(targetEntity="Wallabag\CoreBundle\Entity\Entry", inversedBy="comments")
     * @ORM\JoinColumn(name="entry_id", referencedColumnName="id")
     */
    private $entry;

    /*
     * @param User     $user
     */
    public function __construct(\Wallabag\UserBundle\Entity\User $user)
    {
        $this->user = $user;
        $this->created = new \DateTime();
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
     * Set text.
     *
     * @param string $text
     *
     * @return Comment
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return Comment
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return Comment
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Get quote.
     *
     * @return string
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * Set quote.
     *
     * @param string $quote
     *
     * @return Comment
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;

        return $this;
    }

    /**
     * Get ranges.
     *
     * @return array
     */
    public function getRanges()
    {
        return $this->ranges;
    }

    /**
     * Set ranges.
     *
     * @param array $ranges
     *
     * @return Comment
     */
    public function setRanges($ranges)
    {
        $this->ranges = $ranges;

        return $this;
    }

    /**
     * Set user.
     *
     * @param string $user
     *
     * @return Comment
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @VirtualProperty
     * @SerializedName("user")
     */
    public function getUserName()
    {
        return $this->user->getName();
    }

    /**
     * Set entry.
     *
     * @param Entry $entry
     *
     * @return Comment
     */
    public function setEntry($entry)
    {
        $this->entry = $entry;
        $entry->setComment($this);

        return $this;
    }

    /**
     * Get entry.
     *
     * @return Entry
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @VirtualProperty
     * @SerializedName("annotator_schema_version")
     */
    public function getVersion()
    {
        return 'v1.0';
    }
}
