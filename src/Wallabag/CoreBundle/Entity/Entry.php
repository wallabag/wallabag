<?php

namespace Wallabag\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\XmlRoot;
use Symfony\Component\Validator\Constraints as Assert;
use Wallabag\UserBundle\Entity\User;
<<<<<<< bd561aeb66e1b67a8db188042fbe6d556564341d
<<<<<<< 564b1b10a638ac2b981f05716f84104cd63bd099
<<<<<<< e9a854c48821720618d0c607260ed92a2f43fa37
use Wallabag\AnnotationBundle\Entity\Annotation;
=======
use Wallabag\CommentBundle\Entity\Comment;
>>>>>>> Comment work with annotator v2
=======
use Wallabag\AnnotationBundle\Entity\Annotation;
>>>>>>> Rename CommentBundle with AnnotationBundle
=======
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
use Wallabag\AnnotationBundle\Entity\Annotation;
=======
use Wallabag\CommentBundle\Entity\Comment;
>>>>>>> 0c4e4ab... Comment work with annotator v2
=======
use Wallabag\AnnotationBundle\Entity\Annotation;
>>>>>>> 9e1ce38... Rename CommentBundle with AnnotationBundle
<<<<<<< 89a1cc72d308247cdb2b4cdb832122cafc3f7e87
>>>>>>> Add the timezone as an argument in the docker-compose. For that, need to use v2 of docker-compose (with version >= 1.6.0)
=======
=======
use Wallabag\CommentBundle\Entity\Comment;
>>>>>>> 0c4e4ab... Comment work with annotator v2
>>>>>>> Comment work with annotator v2

/**
 * Entry.
 *
 * @XmlRoot("entry")
 * @ORM\Entity(repositoryClass="Wallabag\CoreBundle\Repository\EntryRepository")
 * @ORM\Table(name="`entry`")
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
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="text", nullable=true)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $title;

    /**
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="url", type="text", nullable=true)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $url;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_archived", type="boolean")
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $isArchived = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_starred", type="boolean")
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $isStarred = false;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $content;

    /**
     * @var date
     *
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @Groups({"export_all"})
     */
    private $createdAt;

    /**
     * @var date
     *
     * @ORM\Column(name="updated_at", type="datetime")
     *
     * @Groups({"export_all"})
     */
    private $updatedAt;

    /**
<<<<<<< bd561aeb66e1b67a8db188042fbe6d556564341d
<<<<<<< 564b1b10a638ac2b981f05716f84104cd63bd099
<<<<<<< e9a854c48821720618d0c607260ed92a2f43fa37
     * @ORM\OneToMany(targetEntity="Wallabag\AnnotationBundle\Entity\Annotation", mappedBy="entry", cascade={"persist", "remove"})
=======
     * @ORM\OneToMany(targetEntity="Wallabag\CommentBundle\Entity\Comment", mappedBy="entry", cascade={"persist", "remove"})
>>>>>>> Comment work with annotator v2
=======
     * @ORM\OneToMany(targetEntity="Wallabag\AnnotationBundle\Entity\Annotation", mappedBy="entry", cascade={"persist", "remove"})
>>>>>>> Rename CommentBundle with AnnotationBundle
=======
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
     * @ORM\OneToMany(targetEntity="Wallabag\AnnotationBundle\Entity\Annotation", mappedBy="entry", cascade={"persist", "remove"})
=======
     * @ORM\OneToMany(targetEntity="Wallabag\CommentBundle\Entity\Comment", mappedBy="entry", cascade={"persist", "remove"})
>>>>>>> 0c4e4ab... Comment work with annotator v2
=======
     * @ORM\OneToMany(targetEntity="Wallabag\AnnotationBundle\Entity\Annotation", mappedBy="entry", cascade={"persist", "remove"})
>>>>>>> 9e1ce38... Rename CommentBundle with AnnotationBundle
<<<<<<< 89a1cc72d308247cdb2b4cdb832122cafc3f7e87
>>>>>>> Add the timezone as an argument in the docker-compose. For that, need to use v2 of docker-compose (with version >= 1.6.0)
=======
=======
     * @ORM\OneToMany(targetEntity="Wallabag\CommentBundle\Entity\Comment", mappedBy="entry", cascade={"persist", "remove"})
>>>>>>> 0c4e4ab... Comment work with annotator v2
>>>>>>> Comment work with annotator v2
     * @ORM\JoinTable
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $annotations;

    /**
     * @var string
     *
     * @ORM\Column(name="mimetype", type="text", nullable=true)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $mimetype;

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="text", nullable=true)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $language;

    /**
     * @var int
     *
     * @ORM\Column(name="reading_time", type="integer", nullable=true)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $readingTime;

    /**
     * @var string
     *
     * @ORM\Column(name="domain_name", type="text", nullable=true)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $domainName;

    /**
     * @var string
     *
     * @ORM\Column(name="preview_picture", type="text", nullable=true)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $previewPicture;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_public", type="boolean", nullable=true, options={"default" = false})
     *
     * @Groups({"export_all"})
     */
    private $isPublic;

    /**
     * @ORM\ManyToOne(targetEntity="Wallabag\UserBundle\Entity\User", inversedBy="entries")
     *
     * @Groups({"export_all"})
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="entries", cascade={"persist", "remove"})
     * @ORM\JoinTable
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $tags;

    /*
     * @param User     $user
     */
    public function __construct(\Wallabag\UserBundle\Entity\User $user)
    {
        $this->user = $user;
        $this->tags = new ArrayCollection();
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
     * @param bool $isArchived
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
     * @return bool
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
     * @param bool $isStarred
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
     * @return bool
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
<<<<<<< bd561aeb66e1b67a8db188042fbe6d556564341d
<<<<<<< 564b1b10a638ac2b981f05716f84104cd63bd099
<<<<<<< e9a854c48821720618d0c607260ed92a2f43fa37
     * @return ArrayCollection<Annotation>
=======
     * @return ArrayCollection<Comment>
>>>>>>> Comment work with annotator v2
=======
     * @return ArrayCollection<Annotation>
>>>>>>> Rename CommentBundle with AnnotationBundle
=======
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
     * @return ArrayCollection<Annotation>
=======
     * @return ArrayCollection<Comment>
>>>>>>> 0c4e4ab... Comment work with annotator v2
=======
     * @return ArrayCollection<Annotation>
>>>>>>> 9e1ce38... Rename CommentBundle with AnnotationBundle
<<<<<<< 89a1cc72d308247cdb2b4cdb832122cafc3f7e87
>>>>>>> Add the timezone as an argument in the docker-compose. For that, need to use v2 of docker-compose (with version >= 1.6.0)
=======
=======
     * @return ArrayCollection<Comment>
>>>>>>> 0c4e4ab... Comment work with annotator v2
>>>>>>> Comment work with annotator v2
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }

    /**
<<<<<<< bd561aeb66e1b67a8db188042fbe6d556564341d
<<<<<<< 564b1b10a638ac2b981f05716f84104cd63bd099
<<<<<<< e9a854c48821720618d0c607260ed92a2f43fa37
=======
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< 89a1cc72d308247cdb2b4cdb832122cafc3f7e87
>>>>>>> Add the timezone as an argument in the docker-compose. For that, need to use v2 of docker-compose (with version >= 1.6.0)
=======
<<<<<<< HEAD
>>>>>>> Comment work with annotator v2
     * @param Annotation $annotation
     */
    public function setAnnotation(Annotation $annotation)
    {
        $this->annotations[] = $annotation;
=======
     * @param Comment $comment
=======
     * @param Annotation $annotation
<<<<<<< bd561aeb66e1b67a8db188042fbe6d556564341d
>>>>>>> Rename CommentBundle with AnnotationBundle
     */
    public function setAnnotation(Annotation $annotation)
    {
<<<<<<< 564b1b10a638ac2b981f05716f84104cd63bd099
        $this->comments[] = $comment;
>>>>>>> Comment work with annotator v2
=======
        $this->annotations[] = $annotation;
>>>>>>> Rename CommentBundle with AnnotationBundle
=======
>>>>>>> 9e1ce38... Rename CommentBundle with AnnotationBundle
     */
    public function setAnnotation(Annotation $annotation)
    {
<<<<<<< HEAD
        $this->comments[] = $comment;
>>>>>>> 0c4e4ab... Comment work with annotator v2
=======
        $this->annotations[] = $annotation;
>>>>>>> 9e1ce38... Rename CommentBundle with AnnotationBundle
<<<<<<< 89a1cc72d308247cdb2b4cdb832122cafc3f7e87
>>>>>>> Add the timezone as an argument in the docker-compose. For that, need to use v2 of docker-compose (with version >= 1.6.0)
=======
=======
     * @param Comment $comment
     */
    public function setComment(Comment $comment)
    {
        $this->comments[] = $comment;
>>>>>>> 0c4e4ab... Comment work with annotator v2
>>>>>>> Comment work with annotator v2
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
        if ($this->tags->contains($tag)) {
            return;
        }

        // check if tag already exist but has not yet be persisted
        // it seems that the previous condition with `contains()` doesn't check that case
        foreach ($this->tags as $existingTag) {
            if ($existingTag->getLabel() === $tag->getLabel()) {
                return;
            }
        }

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
