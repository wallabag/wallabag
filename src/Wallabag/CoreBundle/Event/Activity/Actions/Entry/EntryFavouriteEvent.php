<?php

namespace Wallabag\CoreBundle\Event\Activity\Actions\Entry;

use Symfony\Component\EventDispatcher\Event;
use Wallabag\CoreBundle\Entity\Entry;

/**
 * This event is fired as soon as an entry was favourited.
 */
class EntryFavouriteEvent extends EntryEvent
{
    const NAME = 'entry.favourite';
}
