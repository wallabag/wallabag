<?php

namespace Wallabag\CoreBundle\Event\Activity\Actions\Share;

/**
 * This event is fired as soon as an share is cancelled
 */
class ShareCancelledEvent extends ShareEvent
{
    const NAME = 'share.cancelled';
}
