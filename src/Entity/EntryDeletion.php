<?php

namespace Wallabag\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Wallabag\Repository\EntryDeletionRepository;

/**
 * EntryDeletion.
 *
 * Tracks when entries are deleted for client synchronization purposes.
 */
#[ORM\Table(name: '`entry_deletion`')]
#[ORM\Index(columns: ['deleted_at'])]
#[ORM\Index(columns: ['user_id', 'deleted_at'])]
#[ORM\Entity(repositoryClass: EntryDeletionRepository::class)]
class EntryDeletion
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var int
     */
    #[ORM\Column(name: 'entry_id', type: 'integer')]
    private $entryId;

    /**
     * @var \DateTimeInterface
     */
    #[ORM\Column(name: 'deleted_at', type: 'datetime')]
    private $deletedAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Serializer\Exclude()]
    private $user;

    public function __construct(User $user, int $entryId)
    {
        $this->user = $user;
        $this->entryId = $entryId;
        $this->deletedAt = new \DateTime();
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
     * Get entryId.
     *
     * @return int
     */
    public function getEntryId()
    {
        return $this->entryId;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set deleted_at.
     *
     * @return EntryDeletion
     */
    public function setDeletedAt(\DateTimeInterface $deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Create an EntryDeletion from an Entry that's being deleted.
     */
    public static function createFromEntry(Entry $entry): self
    {
        return new self($entry->getUser(), $entry->getId());
    }
}
