<?php

namespace Wallabag\CoreBundle\Event\Activity\Actions\Share;

use Symfony\Component\EventDispatcher\Event;
use Wallabag\CoreBundle\Entity\Share;

/**
 * This event is fired when share-related stuff is made.
 */
abstract class ShareEvent extends Event
{
    protected $share;

    /**
     * ShareEvent constructor.
     * @param Share $share
     */
    public function __construct(Share $share)
    {
        $this->share = $share;
    }

    /**
     * @return Share
     */
    public function getShare()
    {
        return $this->share;
    }

    /**
     * @param Share $share
     */
    public function setShare(Share $share)
    {
        $this->share = $share;
    }
}
