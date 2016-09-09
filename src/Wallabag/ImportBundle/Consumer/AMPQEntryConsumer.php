<?php

namespace Wallabag\ImportBundle\Consumer;

use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Wallabag\ImportBundle\Import\AbstractImport;
use Wallabag\UserBundle\Repository\UserRepository;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class AMPQEntryConsumer implements ConsumerInterface
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
     * {@inheritdoc}
     */
    public function execute(AMQPMessage $msg)
    {
        $storedEntry = json_decode($msg->body, true);

        $user = $this->userRepository->find($storedEntry['userId']);

        // no user? Drop message
        if (null === $user) {
            $this->logger->warning('Unable to retrieve user', ['entry' => $storedEntry]);

            return;
        }

        $this->import->setUser($user);

        $entry = $this->import->parseEntry($storedEntry);

        if (null === $entry) {
            $this->logger->warning('Unable to parse entry', ['entry' => $storedEntry]);

            return;
        }

        try {
            $this->em->flush();

            // clear only affected entities
            $this->em->clear(Entry::class);
            $this->em->clear(Tag::class);
        } catch (\Exception $e) {
            $this->logger->warning('Unable to save entry', ['entry' => $storedEntry, 'exception' => $e]);

            return;
        }

        $this->logger->info('Content with url ('.$entry->getUrl().') imported !');
    }
}
