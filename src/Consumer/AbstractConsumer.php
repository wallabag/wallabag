<?php

namespace Wallabag\Consumer;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Wallabag\Entity\Entry;
use Wallabag\Entity\Tag;
use Wallabag\Event\EntrySavedEvent;
use Wallabag\Import\AbstractImport;
use Wallabag\Repository\UserRepository;

abstract class AbstractConsumer
{
    protected $em;
    protected $userRepository;
    protected $import;
    protected $eventDispatcher;
    protected $logger;

    public function __construct(EntityManagerInterface $em, UserRepository $userRepository, AbstractImport $import, EventDispatcherInterface $eventDispatcher, ?LoggerInterface $logger = null)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->import = $import;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * Handle a message and save it.
     *
     * @param string $body Message from the queue (in json)
     *
     * @return bool
     */
    protected function handleMessage($body)
    {
        $storedEntry = json_decode($body, true);

        $user = $this->userRepository->find($storedEntry['userId']);

        // no user? Drop message
        if (null === $user) {
            $this->logger->warning('Unable to retrieve user', ['entry' => $storedEntry]);

            // return true to skip message
            return true;
        }

        $this->import->setUser($user);

        if (false === $this->import->validateEntry($storedEntry)) {
            $this->logger->warning('Entry is invalid', ['entry' => $storedEntry]);

            // return true to skip message
            return true;
        }

        $entry = $this->import->parseEntry($storedEntry);

        if (null === $entry) {
            $this->logger->warning('Entry already exists', ['entry' => $storedEntry]);

            // return true to skip message
            return true;
        }

        try {
            $this->em->flush();

            // entry saved, dispatch event about it!
            $this->eventDispatcher->dispatch(new EntrySavedEvent($entry), EntrySavedEvent::NAME);

            // clear only affected entities
            $this->em->clear(Entry::class);
            $this->em->clear(Tag::class);
        } catch (\Exception $e) {
            $this->logger->warning('Unable to save entry', ['entry' => $storedEntry, 'exception' => $e]);

            return false;
        }

        $this->logger->info('Content with url imported! (' . $entry->getUrl() . ')');

        return true;
    }
}
