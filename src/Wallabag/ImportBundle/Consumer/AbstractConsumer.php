<?php

namespace Wallabag\ImportBundle\Consumer;

use Doctrine\ORM\EntityManager;
use Wallabag\ImportBundle\Import\AbstractImport;
use Wallabag\UserBundle\Repository\UserRepository;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractConsumer
{
    protected $em;
    protected $userRepository;
    protected $import;
    protected $logger;

    public function __construct(EntityManager $em, UserRepository $userRepository, AbstractImport $import, LoggerInterface $logger = null)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->import = $import;
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

            return false;
        }

        $this->import->setUser($user);

        $entry = $this->import->parseEntry($storedEntry);

        if (null === $entry) {
            $this->logger->warning('Unable to parse entry', ['entry' => $storedEntry]);

            return false;
        }

        try {
            $this->em->flush();

            // clear only affected entities
            $this->em->clear(Entry::class);
            $this->em->clear(Tag::class);
        } catch (\Exception $e) {
            $this->logger->warning('Unable to save entry', ['entry' => $storedEntry, 'exception' => $e]);

            return false;
        }

        $this->logger->info('Content with url imported! ('.$entry->getUrl().')');

        return true;
    }
}
