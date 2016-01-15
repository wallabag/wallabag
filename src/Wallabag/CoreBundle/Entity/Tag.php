<?php

namespace Wallabag\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * Tag.
 *
 * @XmlRoot("tag")
 * @ORM\Table(name="`tag`")
 * @ORM\Entity(repositoryClass="Wallabag\CoreBundle\Repository\TagRepository")
 * @ExclusionPolicy("all")
 */
class Tag
{
    /**
     * @var int
     *
     * @Expose
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Expose
     * @ORM\Column(name="label", type="text")
     */
    private $label;

    /**
     * @Expose
     * @Gedmo\Slug(fields={"label"})
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    /**
     * @ORM\ManyToMany(targetEntity="Entry", mappedBy="tags", cascade={"persist"})
     */
    private $entries;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->label;
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
     * Set label.
     *
     * @param string $label
     *
     * @return Tag
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function addEntry(Entry $entry)
    {
        $this->entries[] = $entry;
    }

    public function hasEntry($entry)
    {
        return $this->entries->contains($entry);
    }
}
