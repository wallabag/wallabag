<?php

namespace Wallabag\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\XmlRoot;
use Symfony\Component\Validator\Constraints as Assert;
use Wallabag\AnnotationBundle\Entity\Annotation;
use Wallabag\CoreBundle\Helper\EntityTimestampsTrait;
use Wallabag\CoreBundle\Helper\UrlHasher;
use Wallabag\UserBundle\Entity\User;

/**
 * Entry.
 *
 * @XmlRoot("entry")
 * @ORM\Entity(repositoryClass="Wallabag\CoreBundle\Repository\EntryRepository")
 * @ORM\Table(
 *     name="`entry`",
 *     options={"collate"="utf8mb4_unicode_ci", "charset"="utf8mb4"},
 *     indexes={
 *         @ORM\Index(name="created_at", columns={"created_at"}),
 *         @ORM\Index(name="uid", columns={"uid"}),
 *         @ORM\Index(name="hashed_url_user_id", columns={"user_id", "hashed_url"}, options={"lengths"={null, 40}}),
 *         @ORM\Index(name="hashed_given_url_user_id", columns={"user_id", "hashed_given_url"}, options={"lengths"={null, 40}}),
 *         @ORM\Index(name="user_language", columns={"language", "user_id"}),
 *         @ORM\Index(name="user_archived", columns={"user_id", "is_archived", "archived_at"}),
 *         @ORM\Index(name="user_created", columns={"user_id", "created_at"}),
 *         @ORM\Index(name="user_starred", columns={"user_id", "is_starred", "starred_at"})
 *     }
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Hateoas\Relation("self", href = "expr('/api/entries/' ~ object.getId())")
 */
class Entry
{
    use EntityTimestampsTrait;

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
     * @var string|null
     *
     * @ORM\Column(name="uid", type="string", length=23, nullable=true)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $uid;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="text", nullable=true)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $title;

    /**
     * Define the url fetched by wallabag (the final url after potential redirections).
     *
     * @var string
     *
     * @Assert\NotBlank()
     * @ORM\Column(name="url", type="text", nullable=true)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="hashed_url", type="string", length=40, nullable=true)
     */
    private $hashedUrl;

    /**
     * From where user retrieved/found the url (an other article, a twitter, or the given_url if non are provided).
     *
     * @var string
     *
     * @ORM\Column(name="origin_url", type="text", nullable=true)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $originUrl;

    /**
     * Define the url entered by the user (without redirections).
     *
     * @var string
     *
     * @ORM\Column(name="given_url", type="text", nullable=true)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $givenUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="hashed_given_url", type="string", length=40, nullable=true)
     */
    private $hashedGivenUrl;

    /**
     * @var bool
     *
     * @Exclude
     *
     * @ORM\Column(name="is_archived", type="boolean")
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $isArchived = false;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="archived_at", type="datetime", nullable=true)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $archivedAt = null;

    /**
     * @var bool
     *
     * @Exclude
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
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $createdAt;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="updated_at", type="datetime")
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $updatedAt;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="published_at", type="datetime", nullable=true)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $publishedAt;

    /**
     * @var array
     *
     * @ORM\Column(name="published_by", type="array", nullable=true)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $publishedBy;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="starred_at", type="datetime", nullable=true)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $starredAt = null;

    /**
     * @ORM\OneToMany(targetEntity="Wallabag\AnnotationBundle\Entity\Annotation", mappedBy="entry", cascade={"persist", "remove"})
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
     * @ORM\Column(name="language", type="string", length=20, nullable=true)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $language;

    /**
     * @var int
     *
     * @ORM\Column(name="reading_time", type="integer", nullable=false)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $readingTime = 0;

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
     * @var string
     *
     * @ORM\Column(name="http_status", type="string", length=3, nullable=true)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $httpStatus;

    /**
     * @var array
     *
     * @ORM\Column(name="headers", type="array", nullable=true)
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $headers;

    /**
     * @var bool
     *
     * @Exclude
     *
     * @ORM\Column(name="is_not_parsed", type="boolean")
     *
     * @Groups({"entries_for_user", "export_all"})
     */
    private $isNotParsed = false;

    /**
     * @Exclude
     *
     * @ORM\ManyToOne(targetEntity="Wallabag\UserBundle\Entity\User", inversedBy="entries")
     *
     * @Groups({"export_all"})
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="entries", cascade={"persist"})
     * @ORM\JoinTable(
     *  name="entry_tag",
     *  joinColumns={
     *      @ORM\JoinColumn(name="entry_id", referencedColumnName="id", onDelete="cascade")
     *  },
     *  inverseJoinColumns={
     *      @ORM\JoinColumn(name="tag_id", referencedColumnName="id", onDelete="cascade")
     *  }
     * )
     */
    private $tags;

    /*
     * @param User     $user
     */
    public function __construct(User $user)
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
        $this->hashedUrl = UrlHasher::hashUrl($url);

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
     * update isArchived and archive_at fields.
     *
     * @param bool $isArchived
     *
     * @return Entry
     */
    public function updateArchived($isArchived = false)
    {
        $this->setArchived($isArchived);
        $this->setArchivedAt(null);
        if ($this->isArchived()) {
            $this->setArchivedAt(new \DateTime());
        }

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getArchivedAt()
    {
        return $this->archivedAt;
    }

    /**
     * @param \DateTimeInterface|null $archivedAt
     *
     * @return Entry
     */
    public function setArchivedAt($archivedAt = null)
    {
        $this->archivedAt = $archivedAt;

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

    /**
     * @VirtualProperty
     * @SerializedName("is_archived")
     * @Groups({"entries_for_user", "export_all"})
     */
    public function is_Archived()
    {
        return (int) $this->isArchived();
    }

    public function toggleArchive()
    {
        $this->updateArchived($this->isArchived() ^ 1);

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

    /**
     * @VirtualProperty
     * @SerializedName("is_starred")
     * @Groups({"entries_for_user", "export_all"})
     */
    public function is_Starred()
    {
        return (int) $this->isStarred();
    }

    public function toggleStar()
    {
        $this->isStarred = !$this->isStarred();

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
     * @VirtualProperty
     * @SerializedName("user_name")
     */
    public function getUserName()
    {
        return $this->user->getUserName();
    }

    /**
     * @VirtualProperty
     * @SerializedName("user_email")
     */
    public function getUserEmail()
    {
        return $this->user->getEmail();
    }

    /**
     * @VirtualProperty
     * @SerializedName("user_id")
     */
    public function getUserId()
    {
        return $this->user->getId();
    }

    /**
     * Set created_at.
     * Only used when importing data from an other service.
     *
     * @return Entry
     */
    public function setCreatedAt(\DateTimeInterface $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getStarredAt()
    {
        return $this->starredAt;
    }

    /**
     * @param \DateTimeInterface|null $starredAt
     *
     * @return Entry
     */
    public function setStarredAt($starredAt = null)
    {
        $this->starredAt = $starredAt;

        return $this;
    }

    /**
     * update isStarred and starred_at fields.
     *
     * @param bool $isStarred
     *
     * @return Entry
     */
    public function updateStar($isStarred = false)
    {
        $this->setStarred($isStarred);
        $this->setStarredAt(null);
        if ($this->isStarred()) {
            $this->setStarredAt(new \DateTime());
        }

        return $this;
    }

    /**
     * @return ArrayCollection<Annotation>
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }

    public function setAnnotation(Annotation $annotation)
    {
        $this->annotations[] = $annotation;
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
     * @return ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Only used during tests.
     */
    public function getTagsLabel(): array
    {
        $tags = [];
        foreach ($this->tags as $tag) {
            $tags[] = $tag->getLabel();
        }

        return $tags;
    }

    /**
     * @VirtualProperty
     * @SerializedName("tags")
     * @Groups({"entries_for_user", "export_all"})
     */
    public function getSerializedTags()
    {
        $data = [];
        foreach ($this->tags as $tag) {
            $data[] = $tag->getLabel();
        }

        return $data;
    }

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

        $this->tags->add($tag);
    }

    /**
     * Remove the given tag from the entry (if the tag is associated).
     */
    public function removeTag(Tag $tag)
    {
        if (!$this->tags->contains($tag)) {
            return;
        }

        $this->tags->removeElement($tag);
        $tag->removeEntry($this);
    }

    /**
     * Remove all assigned tags from the entry.
     */
    public function removeAllTags()
    {
        foreach ($this->tags as $tag) {
            $this->tags->removeElement($tag);
            $tag->removeEntry($this);
        }
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

    /**
     * Format the entry language to a valid html lang attribute.
     */
    public function getHTMLLanguage()
    {
        $parsedLocale = \Locale::parseLocale($this->getLanguage());
        $lang = '';
        $lang .= $parsedLocale['language'] ?? '';
        $lang .= isset($parsedLocale['region']) ? '-' . $parsedLocale['region'] : '';

        return $lang;
    }

    /**
     * @return string|null
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @param string $uid
     *
     * @return Entry
     */
    public function setUid($uid)
    {
        $this->uid = $uid;

        return $this;
    }

    public function generateUid()
    {
        if (null === $this->uid) {
            // @see http://blog.kevingomez.fr/til/2015/07/26/why-is-uniqid-slow/ for true parameter
            $this->uid = uniqid('', true);
        }
    }

    public function cleanUid()
    {
        $this->uid = null;
    }

    /**
     * Used in the entries filter so it's more explicit for the end user than the uid.
     * Also used in the API.
     *
     * @VirtualProperty
     * @SerializedName("is_public")
     * @Groups({"entries_for_user"})
     *
     * @return bool
     */
    public function isPublic()
    {
        return null !== $this->uid;
    }

    /**
     * @return string
     */
    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    /**
     * @param string $httpStatus
     *
     * @return Entry
     */
    public function setHttpStatus($httpStatus)
    {
        $this->httpStatus = $httpStatus;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * @return Entry
     */
    public function setPublishedAt(\DateTimeInterface $publishedAt)
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * @return array
     */
    public function getPublishedBy()
    {
        return $this->publishedBy;
    }

    /**
     * @param array $publishedBy
     *
     * @return Entry
     */
    public function setPublishedBy($publishedBy)
    {
        $this->publishedBy = $publishedBy;

        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     *
     * @return Entry
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Set origin url.
     *
     * @param string $originUrl
     *
     * @return Entry
     */
    public function setOriginUrl($originUrl)
    {
        $this->originUrl = $originUrl;

        return $this;
    }

    /**
     * Get origin url.
     *
     * @return string
     */
    public function getOriginUrl()
    {
        return $this->originUrl;
    }

    /**
     * Set given url.
     *
     * @param string $givenUrl
     *
     * @return Entry
     */
    public function setGivenUrl($givenUrl)
    {
        $this->givenUrl = $givenUrl;
        $this->hashedGivenUrl = UrlHasher::hashUrl($givenUrl);

        return $this;
    }

    /**
     * Get given url.
     *
     * @return string
     */
    public function getGivenUrl()
    {
        return $this->givenUrl;
    }

    /**
     * @return string
     */
    public function getHashedUrl()
    {
        return $this->hashedUrl;
    }

    /**
     * @param mixed $hashedUrl
     *
     * @return Entry
     */
    public function setHashedUrl($hashedUrl)
    {
        $this->hashedUrl = $hashedUrl;

        return $this;
    }

    /**
     * Set isNotParsed.
     *
     * @param bool $isNotParsed
     *
     * @return Entry
     */
    public function setNotParsed($isNotParsed)
    {
        $this->isNotParsed = $isNotParsed;

        return $this;
    }

    /**
     * Get isNotParsed.
     *
     * @return bool
     */
    public function isNotParsed()
    {
        return $this->isNotParsed;
    }
}
