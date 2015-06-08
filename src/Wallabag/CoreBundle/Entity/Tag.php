<?php

namespace Wallabag\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\XmlRoot;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Tag.
 *
 * @XmlRoot("tag")
 * @ORM\Table
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
     * @ORM\ManyToMany(targetEntity="Entry", mappedBy="tags", cascade={"persist"})
     */
    private $entries;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="tags")
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user    = $user;
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

    public function addEntry(Entry $entry)
    {
        $this->entries[] = $entry;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
