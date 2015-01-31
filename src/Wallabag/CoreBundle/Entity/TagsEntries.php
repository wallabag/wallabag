<?php

namespace Wallabag\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TagsEntries
 *
 * @ORM\Table(name="tags_entries")
 * @ORM\Entity
 */
class TagsEntries
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="entry_id", type="integer", nullable=true)
     */
    private $entryId;

    /**
     * @var integer
     *
     * @ORM\Column(name="tag_id", type="integer", nullable=true)
     */
    private $tagId;

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
     * Set entryId
     *
     * @param  integer     $entryId
     * @return TagsEntries
     */
    public function setEntryId($entryId)
    {
        $this->entryId = $entryId;

        return $this;
    }

    /**
     * Get entryId
     *
     * @return integer
     */
    public function getEntryId()
    {
        return $this->entryId;
    }

    /**
     * Set tagId
     *
     * @param  integer     $tagId
     * @return TagsEntries
     */
    public function setTagId($tagId)
    {
        $this->tagId = $tagId;

        return $this;
    }

    /**
     * Get tagId
     *
     * @return integer
     */
    public function getTagId()
    {
        return $this->tagId;
    }
}
