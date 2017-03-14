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
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
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
            // We don't care about the result of the request, so we
            // execute the call asynchronously in another process:
            $pid = pcntl_fork();
            if (!$pid) {
                $ch = curl_init();
                $hook_replaced = str_replace(["%i", "%t", "%u"],
                                             [(string) $entry->getId(),
                                              urlencode($entry->getTitle()),
                                              urlencode($entry->getUrl())],
                                             $hook);
                curl_setopt($ch, CURLOPT_URL, $hook_replaced);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_exec($ch);
                curl_close($ch);
            }
        }
    }

}
