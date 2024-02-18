<?php

namespace Wallabag\CoreBundle\Guzzle;

use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\SubscriberInterface;

/**
 * Fixes url encoding of a parameter guzzle fails with.
 */
class FixupMondeDiplomatiqueUriSubscriber implements SubscriberInterface
{
    public function getEvents(): array
    {
        return ['complete' => [['fixUri', 500]]];
    }

    public function fixUri(CompleteEvent $event)
    {
        $response = $event->getResponse();

        if (!$response->hasHeader('Location')) {
            return;
        }

        $uri = $response->getHeader('Location');
        if (false === ($badParameter = strstr($uri, 'retour=http://'))) {
            return;
        }

        $response->setHeader('Location', str_replace($badParameter, urlencode($badParameter), $uri));
    }
}
