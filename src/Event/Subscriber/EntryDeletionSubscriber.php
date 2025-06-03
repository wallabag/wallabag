<?php

namespace Wallabag\Event\Subscriber;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Wallabag\Entity\EntryDeletion;
use Wallabag\Event\EntryDeletedEvent;

class EntryDeletionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EntryDeletedEvent::NAME => 'onEntryDeleted',
        ];
    }

    /**
     * Create a deletion event record for the entry.
     */
    public function onEntryDeleted(EntryDeletedEvent $event): void
    {
        $entry = $event->getEntry();

        $deletionEvent = EntryDeletion::createFromEntry($entry);

        $this->em->persist($deletionEvent);
        $this->em->flush();
    }
}
