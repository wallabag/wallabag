<?php

namespace Wallabag\ImportBundle\Component\AMPQ;

use Doctrine\ORM\EntityManager;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Wallabag\CoreBundle\Helper\ContentProxy;
use Wallabag\CoreBundle\Repository\EntryRepository;

class EntryConsumer implements ConsumerInterface
{
    private $em;
    private $contentProxy;
    private $entryRepository;

    public function __construct(EntityManager $em, EntryRepository $entryRepository, ContentProxy $contentProxy)
    {
        $this->em = $em;
        $this->entryRepository = $entryRepository;
        $this->contentProxy = $contentProxy;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(AMQPMessage $msg)
    {
        $storedEntry = unserialize($msg->body);
        $entry = $this->entryRepository->findByUrlAndUserId($storedEntry['url'], $storedEntry['userId']);
        if ($entry) {
            $entry = $this->contentProxy->updateEntry($entry, $entry->getUrl());
            if ($entry) {
                $this->em->persist($entry);
                $this->em->flush();
            }
        }
    }
}
