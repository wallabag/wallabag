<?php

namespace Wallabag\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TagsEntries
 *
 * @ORM\Table(name="tags_entries")
 */
class TagsEntries
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
     *
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="tags_entries")
     * @ORM\JoinColumn(name="entry_id", referencedColumnName="id")
     *
     */
    private $entryId;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Tag", inversedBy="tags_entries")
     * @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
     *
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
     * @return mixed
     */
    public function getEntryId()
    {
        return $this->entryId;
    }

    /**
     * @param mixed $entryId
     */
    public function setEntryId($entryId)
    {
        $this->entryId = $entryId;
    }

    /**
     * @return mixed
     */
    public function getTagId()
    {
        return $this->tagId;
    }

    /**
     * @param mixed $tagId
     */
    public function setTagId($tagId)
    {
        $this->tagId = $tagId;
    }

}
