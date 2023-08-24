<?php

namespace Wallabag\ImportBundle\Import;

use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Event\EntrySavedEvent;

abstract class HtmlImport extends AbstractImport
{
    protected $filepath;

    /**
     * {@inheritdoc}
     */
    abstract public function getName();

    /**
     * {@inheritdoc}
     */
    abstract public function getUrl();

    /**
     * {@inheritdoc}
     */
    abstract public function getDescription();

    /**
     * {@inheritdoc}
     */
    public function import()
    {
        if (!$this->user) {
            $this->logger->error('Wallabag HTML Import: user is not defined');

            return false;
        }

        if (!file_exists($this->filepath) || !is_readable($this->filepath)) {
            $this->logger->error('Wallabag HTML Import: unable to read file', ['filepath' => $this->filepath]);

            return false;
        }

        $html = new \DOMDocument();

        libxml_use_internal_errors(true);
        $html->loadHTMLFile($this->filepath);
        $hrefs = $html->getElementsByTagName('a');
        libxml_use_internal_errors(false);

        if (0 === $hrefs->length) {
            $this->logger->error('Wallabag HTML: no entries in imported file');

            return false;
        }

        $entries = [];
        foreach ($hrefs as $href) {
            $entry = [];
            $entry['url'] = $href->getAttribute('href');
            $entry['tags'] = $href->getAttribute('tags');
            $entry['created_at'] = $href->getAttribute('add_date');
            $entries[] = $entry;
        }

        if ($this->producer) {
            $this->parseEntriesForProducer($entries);

            return true;
        }

        $this->parseEntries($entries);

        return true;
    }

    /**
     * Set file path to the html file.
     *
     * @param string $filepath
     */
    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function parseEntry(array $importedEntry)
    {
        $url = $importedEntry['url'];

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
        $entry->updateArchived($data['is_archived']);
        $createdAt = new \DateTime();
        $createdAt->setTimestamp($data['created_at']);
        $entry->setCreatedAt($createdAt);

        // update entry with content (in case fetching failed, the given entry will be return)
        $this->fetchContent($entry, $data['url'], $data);

        if (\array_key_exists('tags', $data)) {
            $this->tagsAssigner->assignTagsToEntry(
                $entry,
                $data['tags']
            );
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

    /**
     * {@inheritdoc}
     */
    protected function setEntryAsRead(array $importedEntry)
    {
        $importedEntry['is_archived'] = 1;

        return $importedEntry;
    }

    abstract protected function prepareEntry(array $entry = []);
}
