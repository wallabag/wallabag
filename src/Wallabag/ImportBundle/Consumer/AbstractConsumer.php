<?php

namespace Wallabag\ImportBundle\Consumer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Wallabag\ImportBundle\Import\AbstractImport;
use Wallabag\UserBundle\Repository\UserRepository;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Wallabag\CoreBundle\Event\EntrySavedEvent;

abstract class AbstractConsumer
{
    protected $registry;
    protected $userRepository;
    protected $import;
    protected $eventDispatcher;
    protected $logger;

    public function __construct(ManagerRegistry $registry = null, UserRepository $userRepository, AbstractImport $import, EventDispatcherInterface $eventDispatcher, LoggerInterface $logger = null)
    {
        $this->registry = $registry;
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
        // If there is no manager, this means that only Doctrine DBAL is configured
        // In this case we can do nothing and just return
        if (null === $this->registry || !count($this->registry->getManagers())) {
            return false;
        }

        $storedEntry = json_decode($body, true);

        $user = $this->userRepository->find($storedEntry['userId']);

        // no user? Drop message
        if (null === $user) {
            $this->logger->warning('Unable to retrieve user', ['entry' => $storedEntry]);

            // return true to skip message
            return true;
        }

        $this->import->setUser($user);

        $entry = $this->import->parseEntry($storedEntry);

        if (null === $entry) {
            $this->logger->warning('Entry already exists', ['entry' => $storedEntry]);

            // return true to skip message
            return true;
        }

        try {
            $em = $this->registry->getManager();
            $em->flush();

            // entry saved, dispatch event about it!
            $this->eventDispatcher->dispatch(EntrySavedEvent::NAME, new EntrySavedEvent($entry));

            // clear only affected entities
            $em->clear(Entry::class);
            $em->clear(Tag::class);
        } catch (\Exception $e) {
            $this->logger->warning('Unable to save entry', ['entry' => $storedEntry, 'exception' => $e]);

            return false;
        }

        $this->logger->info('Content with url imported! ('.$entry->getUrl().')');

        return true;
    }
}
