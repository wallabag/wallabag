<?php

namespace Wallabag\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * Entry.
 *
 * @XmlRoot("entry")
 * @ORM\Entity(repositoryClass="Wallabag\CoreBundle\Repository\EntryRepository")
 * @ORM\Table
 * @ORM\HasLifecycleCallbacks()
 * @Hateoas\Relation("self", href = "expr('/api/entries/' ~ object.getId())")
 */
class Entry
{
    /** @Serializer\XmlAttribute */
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
     * @var bool
     *
     * @ORM\Column(name="is_archived", type="boolean")
     */
    private $isArchived = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_starred", type="boolean")
     */
    private $isStarred = false;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @var date
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var date
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="entry", cascade={"persist"})
     */
    private $comments;

    /**
     * @var string
     *
     * @ORM\Column(name="mimetype", type="text", nullable=true)
     */
    private $mimetype;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="text", nullable=true)
     */
    private $language;

    /**
     * @var int
     *
     * @ORM\Column(name="reading_time", type="integer", nullable=true)
     */
    private $readingTime;

    /**
     * @var string
     *
     * @ORM\Column(name="domain_name", type="text", nullable=true)
     */
    private $domainName;

    /**
     * @var string
     *
     * @ORM\Column(name="preview_picture", type="text", nullable=true)
     */
    private $previewPicture;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_public", type="boolean", nullable=true, options={"default" = false})
     */
    private $isPublic;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="entries")
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="entries", cascade={"persist"})
     * @ORM\JoinTable
     */
    private $tags;

    /*
     * @param User     $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->tags = new ArrayCollection();
        $this->comments = new ArrayCollection();
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
     * Set title.
     *
     * @param string $title
     *
     * @return Entry
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set url.
     *
     * @param string $url
     *
     * @return Entry
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set isArchived.
     *
     * @param string $isArchived
     *
     * @return Entry
     */
    public function setArchived($isArchived)
    {
        $this->isArchived = $isArchived;

        return $this;
    }

    /**
     * Get isArchived.
     *
     * @return string
     */
    public function isArchived()
    {
        return $this->isArchived;
    }

    public function toggleArchive()
    {
        $this->isArchived = $this->isArchived() ^ 1;

        return $this;
    }

    /**
     * Set isStarred.
     *
     * @param string $isStarred
     *
     * @return Entry
     */
    public function setStarred($isStarred)
    {
        $this->isStarred = $isStarred;

        return $this;
    }

    /**
     * Get isStarred.
     *
     * @return string
     */
    public function isStarred()
    {
        return $this->isStarred;
    }

    public function toggleStar()
    {
        $this->isStarred = $this->isStarred() ^ 1;

        return $this;
    }

    /**
     * Set content.
     *
     * @param string $content
     *
     * @return Entry
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function timestamps()
    {
        if (is_null($this->createdAt)) {
            $this->createdAt = new \DateTime();
        }

        $this->updatedAt = new \DateTime();
    }

    /**
     * @return ArrayCollection<Comment>
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param Comment $comment
     */
    public function addComment(Comment $comment)
    {
        $this->comments[] = $comment;
        $comment->setEntry($this);
    }

    /**
     * @param Comment $comment
     */
    public function removeComment(Comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * @return string
     */
    public function getMimetype()
    {
        return $this->mimetype;
    }

    /**
     * @param string $mimetype
     */
    public function setMimetype($mimetype)
    {
        $this->mimetype = $mimetype;
    }

    /**
     * @return int
     */
    public function getReadingTime()
    {
        return $this->readingTime;
    }

    /**
     * @param int $readingTime
     */
    public function setReadingTime($readingTime)
    {
        $this->readingTime = $readingTime;
    }

    /**
     * @return string
     */
    public function getDomainName()
    {
        return $this->domainName;
    }

    /**
     * @param string $domainName
     */
    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        return $this->isPublic;
    }

    /**
     * @param bool $isPublic
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;
    }

    /**
     * @return ArrayCollection<Tag>
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param Tag $tag
     */
    public function addTag(Tag $tag)
    {
        $this->tags[] = $tag;
        $tag->addEntry($this);
    }

    public function removeTag(Tag $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Set previewPicture.
     *
     * @param string $previewPicture
     *
     * @return Entry
     */
    public function setPreviewPicture($previewPicture)
    {
        $this->previewPicture = $previewPicture;

        return $this;
    }

    /**
     * Get previewPicture.
     *
     * @return string
     */
    public function getPreviewPicture()
    {
        return $this->previewPicture;
    }

    /**
     * Set language.
     *
     * @param string $language
     *
     * @return Entry
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
}
