<?php

namespace Wallabag\ImportBundle\Import;

use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\CoreBundle\Event\EntrySavedEvent;
use Wallabag\CoreBundle\Helper\ContentProxy;
use Wallabag\CoreBundle\Helper\TagsAssigner;
use Wallabag\UserBundle\Entity\User;

abstract class AbstractImport implements ImportInterface
{
    protected $em;
    protected $logger;
    protected $contentProxy;
    protected $tagsAssigner;
    protected $eventDispatcher;
    protected $producer;
    protected $user;
    protected $markAsRead;
    protected $disableContentUpdate = false;
    protected $skippedEntries = 0;
    protected $importedEntries = 0;
    protected $queuedEntries = 0;

    public function __construct(EntityManagerInterface $em, ContentProxy $contentProxy, TagsAssigner $tagsAssigner, EventDispatcherInterface $eventDispatcher, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->contentProxy = $contentProxy;
        $this->tagsAssigner = $tagsAssigner;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Set RabbitMQ/Redis Producer to send each entry to a queue.
     * This method should be called when user has enabled RabbitMQ.
     */
    public function setProducer(ProducerInterface $producer)
    {
        $this->producer = $producer;
    }

    /**
     * Set current user.
     * Could the current *connected* user or one retrieve by the consumer.
     */
    public function setUser(User $user)
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
     * Set whether articles should be fetched for updated content.
     *
     * @param bool $disableContentUpdate
     */
    public function setDisableContentUpdate($disableContentUpdate)
    {
        $this->disableContentUpdate = $disableContentUpdate;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSummary()
    {
        return [
            'skipped' => $this->skippedEntries,
            'imported' => $this->importedEntries,
            'queued' => $this->queuedEntries,
        ];
    }

    /**
     * Parse one entry.
     *
     * @return Entry|null
     */
    abstract public function parseEntry(array $importedEntry);

    /**
     * Validate that an entry is valid (like has some required keys, etc.).
     *
     * @return bool
     */
    abstract public function validateEntry(array $importedEntry);

    /**
     * Fetch content from the ContentProxy (using graby).
     * If it fails return the given entry to be saved in all case (to avoid user to loose the content).
     *
     * @param Entry  $entry   Entry to update
     * @param string $url     Url to grab content for
     * @param array  $content An array with AT LEAST keys title, html, url, language & content_type to skip the fetchContent from the url
     */
    protected function fetchContent(Entry $entry, $url, array $content = [])
    {
        try {
            $this->contentProxy->updateEntry($entry, $url, $content, $this->disableContentUpdate);
        } catch (\Exception $e) {
            $this->logger->error('Error trying to import an entry.', [
                'entry_url' => $url,
                'error_msg' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Parse and insert all given entries.
     */
    protected function parseEntries(array $entries)
    {
        $i = 1;
        $entryToBeFlushed = [];

        foreach ($entries as $importedEntry) {
            if ($this->markAsRead) {
                $importedEntry = $this->setEntryAsRead($importedEntry);
            }

            if (false === $this->validateEntry($importedEntry)) {
                continue;
            }

            $entry = $this->parseEntry($importedEntry);

            if (null === $entry) {
                continue;
            }

            // store each entry to be flushed so we can trigger the entry.saved event for each of them
            // entry.saved needs the entry to be persisted in db because it needs it id to generate
            // images (at least)
            $entryToBeFlushed[] = $entry;

            // flush every 20 entries
            if (0 === ($i % 20)) {
                $this->em->flush();

                foreach ($entryToBeFlushed as $entry) {
                    $this->eventDispatcher->dispatch(new EntrySavedEvent($entry), EntrySavedEvent::NAME);
                }

                $entryToBeFlushed = [];

                // clear only affected entities
                $this->em->clear(Entry::class);
                $this->em->clear(Tag::class);
            }
            ++$i;
        }

        $this->em->flush();

        if (!empty($entryToBeFlushed)) {
            foreach ($entryToBeFlushed as $entry) {
                $this->eventDispatcher->dispatch(new EntrySavedEvent($entry), EntrySavedEvent::NAME);
            }
        }
    }

    /**
     * Parse entries and send them to the queue.
     * It should just be a simple loop on all item, no call to the database should be done
     * to speedup queuing.
     *
     * Faster parse entries for Producer.
     * We don't care to make check at this time. They'll be done by the consumer.
     */
    protected function parseEntriesForProducer(array $entries)
    {
        foreach ($entries as $importedEntry) {
            // set userId for the producer (it won't know which user is connected)
            $importedEntry['userId'] = $this->user->getId();

            if ($this->markAsRead) {
                $importedEntry = $this->setEntryAsRead($importedEntry);
            }

            ++$this->queuedEntries;

            $this->producer->publish(json_encode($importedEntry));
        }
    }

    /**
     * Set current imported entry to archived / read.
     * Implementation is different accross all imports.
     *
     * @return array
     */
    abstract protected function setEntryAsRead(array $importedEntry);
}
