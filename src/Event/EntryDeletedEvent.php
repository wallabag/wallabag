<?php

namespace Wallabag\CoreBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Wallabag\CoreBundle\Entity\Entry;

/**
 * This event is fired as soon as an entry is deleted.
 */
class EntryDeletedEvent extends Event
{
    public const NAME = 'entry.deleted';

    protected $entry;

    public function __construct(Entry $entry)
    {
        $this->entry = $entry;
    }

    public function getEntry(): Entry
    {
        return $this->entry;
    }
}
