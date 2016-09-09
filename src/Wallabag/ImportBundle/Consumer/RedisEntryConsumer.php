<?php

namespace Wallabag\ImportBundle\Consumer;

use Simpleue\Job\Job;
use Doctrine\ORM\EntityManager;
use Wallabag\ImportBundle\Import\AbstractImport;
use Wallabag\UserBundle\Repository\UserRepository;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class RedisEntryConsumer implements Job
{
    private $em;
    private $userRepository;
    private $import;
    private $logger;

    public function __construct(EntityManager $em, UserRepository $userRepository, AbstractImport $import, LoggerInterface $logger = null)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->import = $import;
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * Handle one message by one message.
     *
     * @param string $job Content of the message (directly from Redis)
     *
     * @return bool
     */
    public function manage($job)
    {
        $storedEntry = json_decode($job, true);

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

        $this->logger->info('Content with url ('.$entry->getUrl().') imported !');

        return true;
    }

    /**
     * Should tell if the given job will kill the worker.
     * We don't want to stop it :).
     */
    public function isStopJob($job)
    {
        return false;
    }
}
