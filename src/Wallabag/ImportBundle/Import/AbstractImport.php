<?php

namespace Wallabag\ImportBundle\Import;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Doctrine\ORM\EntityManager;
use Wallabag\CoreBundle\Helper\ContentProxy;
use Wallabag\CoreBundle\Entity\Entry;
use Symfony\Component\Security\Core\User\UserInterface;
use OldSound\RabbitMqBundle\RabbitMq\Producer;

abstract class AbstractImport implements ImportInterface
{
    protected $em;
    protected $logger;
    protected $contentProxy;
    protected $producer;
    protected $user;
    protected $markAsRead;

    public function __construct(EntityManager $em, ContentProxy $contentProxy)
    {
        $this->em = $em;
        $this->logger = new NullLogger();
        $this->contentProxy = $contentProxy;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Set RabbitMQ Producer to send each entry to a queue.
     * This method should be called when user has enabled RabbitMQ.
     *
     * @param Producer $producer
     */
    public function setRabbitmqProducer(Producer $producer)
    {
        $this->producer = $producer;
    }

    /**
     * Set current user.
     * Could the current *connected* user or one retrieve by the consumer.
     *
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * Set whether articles must be all marked as read.
     *
     * @param bool $markAsRead
     */
    public function setMarkAsRead($markAsRead)
    {
        $this->markAsRead = $markAsRead;

        return $this;
    }

    /**
     * Get whether articles must be all marked as read.
     */
    public function getMarkAsRead()
    {
        return $this->markAsRead;
    }

    /**
     * Fetch content from the ContentProxy (using graby).
     * If it fails return false instead of the updated entry.
     *
     * @param Entry  $entry   Entry to update
     * @param string $url     Url to grab content for
     * @param array  $content An array with AT LEAST keys title, html, url, language & content_type to skip the fetchContent from the url
     *
     * @return Entry|false
     */
    protected function fetchContent(Entry $entry, $url, array $content = [])
    {
        try {
            return $this->contentProxy->updateEntry($entry, $url, $content);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Parse and insert all given entries.
     *
     * @param $entries
     */
    protected function parseEntries($entries)
    {
        $i = 1;

        foreach ($entries as $importedEntry) {
            $entry = $this->parseEntry($importedEntry);

            if (null === $entry) {
                continue;
            }

            // flush every 20 entries
            if (($i % 20) === 0) {
                $this->em->flush();
                $this->em->clear($entry);
            }
            ++$i;
        }

        $this->em->flush();
    }

    /**
     * Parse one entry.
     *
     * @param array $importedEntry
     *
     * @return Entry
     */
    abstract public function parseEntry(array $importedEntry);
}
