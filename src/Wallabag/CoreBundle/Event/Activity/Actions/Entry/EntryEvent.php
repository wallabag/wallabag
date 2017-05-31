<?php

namespace Wallabag\CoreBundle\Event\Activity\Actions\Entry;

use Symfony\Component\EventDispatcher\Event;
use Wallabag\CoreBundle\Entity\Entry;

/**
 * This event is fired when entry-related stuff is made.
 */
abstract class EntryEvent extends Event
{
    protected $entry;

    /**
     * EntryEvent constructor.
     * @param Entry $entry
     */
    public function __construct(Entry $entry)
    {
        $this->entry = $entry;
    }

    /**
     * @return Entry
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @param Entry $entry
     * @return EntryEvent
     */
    public function setEntry(Entry $entry)
    {
        $this->entry = $entry;
        return $this;
    }
}
