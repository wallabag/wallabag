<?php

namespace Wallabag\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Change.
 *
 * This entity stores a datetime for each event (updated or tagged) done on an entry.
 *
 * @ORM\Entity(repositoryClass="Wallabag\CoreBundle\Repository\ChangeRepository")
 * @ORM\Table(name="`change`")
 */
class Change
{
    const MODIFIED_TYPE = 1;
    const CHANGED_TAG_TYPE = 2;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="Wallabag\CoreBundle\Entity\Entry", inversedBy="changes")
     */
    private $entry;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    public function __construct($type, Entry $entry)
    {
        $this->type = $type;
        $this->entry = $entry;
        $this->createdAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return Entry
     */
    public function getEntry()
    {
        return $this->entry;
    }
}
