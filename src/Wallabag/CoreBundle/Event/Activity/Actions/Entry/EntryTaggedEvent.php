<?php

namespace Wallabag\CoreBundle\Event\Activity\Actions\Entry;

use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;

/**
 * This event is fired as soon as a tag is added on an entry.
 */
class EntryTaggedEvent extends EntryEvent
{
    const NAME = 'entry.tagged';

    /** @var Tag[] */
    protected $tags;

    /**
     * @var boolean
     */
    protected $remove;

    /**
     * EntryTaggedEvent constructor.
     * @param Entry $entry
     * @param $tags
     * @param bool $remove
     */
    public function __construct(Entry $entry, $tags, $remove = false)
    {
        parent::__construct($entry);

        if (false === is_array($tags)) {
            $tags = [$tags];
        }

        $this->tags = $tags;
    }

    /**
     * @return Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @return bool
     */
    public function isRemove()
    {
        return $this->remove;
    }
}
