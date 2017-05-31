<?php

namespace Wallabag\CoreBundle\Event\Activity\Actions\Entry;

/**
 * This event is fired as soon as an entry was edited.
 */
class EntryEditedEvent extends EntryEvent
{
    const NAME = 'entry.edited';
}
