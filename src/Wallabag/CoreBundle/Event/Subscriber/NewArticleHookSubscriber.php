<?php

namespace Wallabag\CoreBundle\Event\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;
use Wallabag\CoreBundle\Helper\DownloadImages;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Config;
use Wallabag\CoreBundle\Event\EntrySavedEvent;
use Wallabag\CoreBundle\Event\EntryDeletedEvent;
use Doctrine\ORM\EntityManager;

class NewArticleHookSubscriber implements EventSubscriberInterface
{
    private $client;
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->client = new \GuzzleHttp\Client;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            EntrySavedEvent::NAME => 'onEntrySaved',
        ];
    }

    /**
     * Call newArticleHook, if set
     *
     * @param EntrySavedEvent $event
     */
    public function onEntrySaved(EntrySavedEvent $event)
    {
        $entry = $event->getEntry();
        $user = $entry->getUser();
        $hook = $entry->getUser()->getConfig()->getNewArticleHook();
        if ('' !== trim($hook)) {
            $url = str_replace(["%i", "%t", "%u"],
                               [(string) $entry->getId(),
                                urlencode($entry->getTitle()),
                                urlencode($entry->getUrl())],
                               $hook);
            // We don't care about the result of the request, so we
            // execute the call asynchronously.
            // We abuse the timeout parameter here since asynchronous
            // requests still caused this to block for some reason.
            try {
                $this->client->get($url, ['timeout' => 0.01]);
            } catch (\GuzzleHttp\Exception\ConnectException $e) {
            }
        }
    }
}
