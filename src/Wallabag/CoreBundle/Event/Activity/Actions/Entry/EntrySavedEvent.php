<?php

namespace Wallabag\CoreBundle\Event\Activity\Actions\Entry;

/**
 * This event is fired as soon as an entry was saved.
 */
class EntrySavedEvent extends EntryEvent
{
    const NAME = 'entry.saved';
}
