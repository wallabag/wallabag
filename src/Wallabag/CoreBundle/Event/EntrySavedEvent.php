<?php

namespace Wallabag\CoreBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Wallabag\CoreBundle\Entity\Entry;

/**
 * This event is fired as soon as an entry was saved.
 */
class EntrySavedEvent extends Event
{
    public const NAME = 'entry.saved';

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
