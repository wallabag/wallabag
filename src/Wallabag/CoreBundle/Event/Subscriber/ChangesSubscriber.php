<?php

namespace Wallabag\CoreBundle\Event\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Wallabag\CoreBundle\Entity\Change;
use Wallabag\CoreBundle\Event\EntryTaggedEvent;
use Wallabag\CoreBundle\Event\EntryUpdatedEvent;

class ChangesSubscriber implements EventSubscriberInterface
{
    /** @var LoggerInterface $logger */
    private $logger;

    /** @var EntityManager $em */
    private $em;

    public function __construct(EntityManager $em, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            EntryUpdatedEvent::NAME => 'onEntryUpdated',
            EntryTaggedEvent::NAME => 'onEntryTagged',
        ];
    }

    /**
     * @param EntryUpdatedEvent $event
     */
    public function onEntryUpdated(EntryUpdatedEvent $event)
    {
        $change = new Change(Change::MODIFIED_TYPE, $event->getEntry());

        $this->em->persist($change);
        $this->em->flush();

        $this->logger->debug('saved updated entry '.$event->getEntry()->getId().' event ');
    }

    /**
     * @param EntryTaggedEvent $event
     */
    public function onEntryTagged(EntryTaggedEvent $event)
    {
        $change = new Change(Change::CHANGED_TAG_TYPE, $event->getEntry());

        $this->em->persist($change);
        $this->em->flush();

        $this->logger->debug('saved (un)tagged entry '.$event->getEntry()->getId().' event ');
    }
}
