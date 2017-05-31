<?php

namespace Wallabag\CoreBundle\Event\Activity\Actions\Entry;

/**
 * This event is fired as soon as an entry is deleted.
 */
class EntryDeletedEvent extends EntryEvent
{
    const NAME = 'entry.deleted';
}
