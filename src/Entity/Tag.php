<?php

namespace Wallabag\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\XmlRoot;
use Wallabag\Repository\TagRepository;

/**
 * Tag.
 */
#[ORM\Table(name: '`tag`')]
#[ORM\Index(columns: ['label'])]
#[ORM\Entity(repositoryClass: TagRepository::class)]
#[XmlRoot('tag')]
#[ExclusionPolicy('all')]
class Tag implements \Stringable
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Expose]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'label', type: 'text')]
    #[Expose]
    private $label;

    #[ORM\Column(length: 128, unique: true)]
    #[Gedmo\Slug(fields: ['label'], prefix: 't:')]
    #[Expose]
    private $slug;

    /**
     * @var Collection<Entry>
     */
    #[ORM\ManyToMany(targetEntity: Entry::class, mappedBy: 'tags', cascade: ['persist'])]
    private Collection $entries;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    public function __toString(): string
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
        $this->label = mb_convert_case($label, \MB_CASE_LOWER);

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
        if ($this->entries->contains($entry)) {
            return;
        }

        $this->entries->add($entry);
        $entry->addTag($this);
    }

    public function removeEntry(Entry $entry)
    {
        if (!$this->entries->contains($entry)) {
            return;
        }

        $this->entries->removeElement($entry);
        $entry->removeTag($this);
    }

    public function hasEntry($entry)
    {
        return $this->entries->contains($entry);
    }

    /**
     * Get entries for this tag.
     *
     * @return Collection<Entry>
     */
    public function getEntries()
    {
        return $this->entries;
    }

    public function getEntriesByUserId($userId)
    {
        $filteredEntries = new ArrayCollection();
        foreach ($this->entries as $entry) {
            if ($entry->getUser()->getId() === $userId) {
                $filteredEntries->add($entry);
            }
        }

        return $filteredEntries;
    }
}
