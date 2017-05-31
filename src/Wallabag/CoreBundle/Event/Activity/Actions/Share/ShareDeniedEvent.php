<?php

namespace Wallabag\CoreBundle\Event\Activity\Actions\Share;

/**
 * This event is fired as soon as an share is denied
 */
class ShareDeniedEvent extends ShareEvent
{
    const NAME = 'share.denied';
}
