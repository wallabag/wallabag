<?php

namespace Wallabag\Import;

use Doctrine\ORM\EntityManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Event\EntrySavedEvent;
use Wallabag\Helper\ContentProxy;
use Wallabag\Helper\TagsAssigner;

abstract class AbstractImport implements ImportInterface
{
    protected $producer;
    protected $user;
    protected $markAsRead;
    protected $disableContentUpdate = false;
    protected $skippedEntries = 0;
    protected $importedEntries = 0;
    protected $queuedEntries = 0;

    public function __construct(
        protected EntityManagerInterface $em,
        protected ContentProxy $contentProxy,
        protected TagsAssigner $tagsAssigner,
        protected EventDispatcherInterface $eventDispatcher,
        protected LoggerInterface $logger,
    ) {
    }

    public function setLogger(LoggerInterface $logger): void
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
    public function setMarkAsRead($markAsRead): static
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

    public function getSummary()
    {
        return [
            'skipped' => $this->skippedEntries,
            'imported' => $this->importedEntries,
            'queued' => $this->queuedEntries,
        ];
    }

    public function setFilepath($filepath): static
    {
        return $this;
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

                $this->em->clear();
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
     * Implementation is different across all imports.
     *
     * @return array
     */
    abstract protected function setEntryAsRead(array $importedEntry);
}
