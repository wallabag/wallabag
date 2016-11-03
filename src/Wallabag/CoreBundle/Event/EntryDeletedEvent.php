<?php

namespace Wallabag\CoreBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Wallabag\CoreBundle\Entity\Entry;

/**
 * This event is fired as soon as an entry is deleted.
 */
class EntryDeletedEvent extends Event
{
    const NAME = 'entry.deleted';

    protected $entry;

    public function __construct(Entry $entry)
    {
        $this->entry = $entry;
    }

    public function getEntry()
    {
        return $this->entry;
    }
}
