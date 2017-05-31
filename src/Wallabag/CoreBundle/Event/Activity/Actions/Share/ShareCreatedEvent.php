<?php

namespace Wallabag\CoreBundle\Event\Activity\Actions\Share;

/**
 * This event is fired as soon as a share is created.
 */
class ShareCreatedEvent extends ShareEvent
{
    const NAME = 'share.created';
}
