<?php

namespace Tests\Wallabag\Event\Subscriber;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Wallabag\Entity\Entry;
use Wallabag\Entity\EntryDeletion;
use Wallabag\Entity\User;
use Wallabag\Event\EntryDeletedEvent;
use Wallabag\Event\Subscriber\EntryDeletionSubscriber;

class EntryDeletionSubscriberTest extends TestCase
{
    public function testEntryDeletionCreatedWhenEntryDeleted(): void
    {
        $user = new User();
        $entry = new Entry($user);

        // the subscriber expects a previously persisted Entry to work
        $reflectedEntry = new \ReflectionClass($entry);
        $property = $reflectedEntry->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($entry, 123);

        $em = $this->createMock(EntityManagerInterface::class);

        // when the event is triggered, an EntryDeletion should be persisted and flushed
        $em->expects($this->once())
            ->method('persist')
            ->with($this->callback(function ($deletion) use ($entry) {
                return $deletion instanceof EntryDeletion
                    && $deletion->getEntryId() === $entry->getId()
                    && $deletion->getUser() === $entry->getUser();
            }));
        $em->expects($this->atLeastOnce())
            ->method('flush');

        // trigger the event to run the mocked up persist and flush
        /** @var EntityManagerInterface $em */
        $subscriber = new EntryDeletionSubscriber($em);
        $event = new EntryDeletedEvent($entry);
        $subscriber->onEntryDeleted($event);
    }
}
