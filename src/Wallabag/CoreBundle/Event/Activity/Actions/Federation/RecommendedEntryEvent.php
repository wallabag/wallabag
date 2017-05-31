<?php

namespace Wallabag\CoreBundle\Event\Activity\Actions\Federation;

use Symfony\Component\EventDispatcher\Event;
use Wallabag\CoreBundle\Entity\Entry;

/**
 * This event is fired as soon as an entry was recommended.
 */
class RecommendedEntryEvent extends Event
{
    const NAME = 'federation.recommend';

    protected $entry;

    /**
     * FederationEvent constructor.
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
     * @return RecommendedEntryEvent
     */
    public function setEntry(Entry $entry)
    {
        $this->entry = $entry;
        return $this;
    }
}
