<?php

namespace Wallabag\Import;

use Wallabag\Entity\Entry;
use Wallabag\Event\EntrySavedEvent;

abstract class BrowserImport extends AbstractImport
{
    protected $filepath;

    abstract public function getName();

    abstract public function getUrl();

    abstract public function getDescription();

    public function import()
    {
        if (!$this->user) {
            $this->logger->error('Wallabag Browser Import: user is not defined');

            return false;
        }

        if (!file_exists($this->filepath) || !is_readable($this->filepath)) {
            $this->logger->error('Wallabag Browser Import: unable to read file', ['filepath' => $this->filepath]);

            return false;
        }

        $data = json_decode(file_get_contents($this->filepath), true);

        if (empty($data)) {
            $this->logger->error('Wallabag Browser: no entries in imported file');

            return false;
        }

        if ($this->producer) {
            $this->parseEntriesForProducer($data);

            return true;
        }

        $this->parseEntries($data);

        return true;
    }

    /**
     * Set file path to the json file.
     *
     * @param string $filepath
     */
    public function setFilepath($filepath): static
    {
        $this->filepath = $filepath;

        return $this;
    }

    public function parseEntry(array $importedEntry)
    {
        if ((!\array_key_exists('guid', $importedEntry) || (!\array_key_exists('id', $importedEntry))) && \is_array(reset($importedEntry))) {
            if ($this->producer) {
                $this->parseEntriesForProducer($importedEntry);

                return null;
            }

            $this->parseEntries($importedEntry);

            return null;
        }

        if (\array_key_exists('children', $importedEntry)) {
            if ($this->producer) {
                $this->parseEntriesForProducer($importedEntry['children']);

                return null;
            }

            $this->parseEntries($importedEntry['children']);

            return null;
        }

        if (!\array_key_exists('uri', $importedEntry) && !\array_key_exists('url', $importedEntry)) {
            return null;
        }

        $url = \array_key_exists('uri', $importedEntry) ? $importedEntry['uri'] : $importedEntry['url'];

        $existingEntry = $this->em
            ->getRepository(Entry::class)
            ->findByUrlAndUserId($url, $this->user->getId());

        if (false !== $existingEntry) {
            ++$this->skippedEntries;

            return null;
        }

        $data = $this->prepareEntry($importedEntry);

        $entry = new Entry($this->user);
        $entry->setUrl($data['url']);
        $entry->setTitle($data['title']);

        // update entry with content (in case fetching failed, the given entry will be return)
        $this->fetchContent($entry, $data['url'], $data);

        if (\array_key_exists('tags', $data)) {
            $this->tagsAssigner->assignTagsToEntry(
                $entry,
                $data['tags']
            );
        }

        $entry->updateArchived($data['is_archived']);

        if (!empty($data['created_at'])) {
            $dt = new \DateTime();
            $entry->setCreatedAt($dt->setTimestamp($data['created_at']));
        }

        $this->em->persist($entry);
        ++$this->importedEntries;

        return $entry;
    }

    /**
     * Parse and insert all given entries.
     */
    protected function parseEntries(array $entries)
    {
        $i = 1;
        $entryToBeFlushed = [];

        foreach ($entries as $importedEntry) {
            if ((array) $importedEntry !== $importedEntry) {
                continue;
            }

            $entry = $this->parseEntry($importedEntry);

            if (null === $entry) {
                continue;
            }

            // @see AbstractImport
            $entryToBeFlushed[] = $entry;

            // flush every 20 entries
            if (0 === ($i % 20)) {
                $this->em->flush();

                foreach ($entryToBeFlushed as $entry) {
                    $this->eventDispatcher->dispatch(new EntrySavedEvent($entry), EntrySavedEvent::NAME);
                }

                $entryToBeFlushed = [];
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
            if ((array) $importedEntry !== $importedEntry) {
                continue;
            }

            // set userId for the producer (it won't know which user is connected)
            $importedEntry['userId'] = $this->user->getId();

            if ($this->markAsRead) {
                $importedEntry = $this->setEntryAsRead($importedEntry);
            }

            ++$this->queuedEntries;

            $this->producer->publish(json_encode($importedEntry));
        }
    }

    protected function setEntryAsRead(array $importedEntry)
    {
        $importedEntry['is_archived'] = 1;

        return $importedEntry;
    }

    abstract protected function prepareEntry(array $entry = []);
}
