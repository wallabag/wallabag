<?php

namespace Wallabag\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Wallabag\Entity\Entry;

/**
 * This event is fired as soon as an entry is deleted.
 */
class EntryDeletedEvent extends Event
{
    public const NAME = 'entry.deleted';

    public function __construct(
        protected Entry $entry,
    ) {
    }

    public function getEntry(): Entry
    {
        return $this->entry;
    }
}
