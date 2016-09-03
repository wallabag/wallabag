<?php

namespace Wallabag\ImportBundle\Consumer\AMPQ;

use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Wallabag\ImportBundle\Import\PocketImport;
use Wallabag\UserBundle\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class PocketConsumer implements ConsumerInterface
{
    private $em;
    private $userRepository;
    private $pocketImport;
    private $logger;

    public function __construct(EntityManager $em, UserRepository $userRepository, PocketImport $pocketImport, LoggerInterface $logger = null)
    {
        $this->em = $em;
        $this->userRepository = $userRepository;
        $this->pocketImport = $pocketImport;
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

        $this->pocketImport->setUser($user);

        $entry = $this->pocketImport->parseEntry($storedEntry);

        if (null === $entry) {
            $this->logger->warning('Unable to parse entry', ['entry' => $storedEntry]);

            return;
        }

        try {
            $this->em->flush();
            $this->em->clear($entry);
        } catch (\Exception $e) {
            $this->logger->warning('Unable to save entry', ['entry' => $storedEntry, 'exception' => $e]);

            return;
        }
    }
}
