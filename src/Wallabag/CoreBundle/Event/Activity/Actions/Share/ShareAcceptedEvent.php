<?php

namespace Wallabag\CoreBundle\Event\Activity\Actions\Share;

/**
 * This event is fired as soon as an share is accepted
 */
class ShareAcceptedEvent extends ShareEvent
{
    const NAME = 'share.accepted';
}
