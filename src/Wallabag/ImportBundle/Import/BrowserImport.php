<?php

namespace Wallabag\ImportBundle\Import;

use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\UserBundle\Entity\User;
use Wallabag\CoreBundle\Helper\ContentProxy;

abstract class BrowserImport extends AbstractImport
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
            $this->logger->error('Wallabag Browser Import: user is not defined');

            return false;
        }

        if (!file_exists($this->filepath) || !is_readable($this->filepath)) {
            $this->logger->error('Wallabag Browser Import: unable to read file', ['filepath' => $this->filepath]);

            return false;
        }

        $data = json_decode(file_get_contents($this->filepath), true);

        if (empty($data)) {
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
    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;

        return $this;
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
            if ((array) $importedEntry !== $importedEntry) {
                continue;
            }

            $entry = $this->parseEntry($importedEntry);

            if (null === $entry) {
                continue;
            }

            // flush every 20 entries
            if (($i % 20) === 0) {
                $this->em->flush();

                // clear only affected entities
                $this->em->clear(Entry::class);
                $this->em->clear(Tag::class);
            }
            ++$i;
        }

        $this->em->flush();
    }

    /**
     * Parse entries and send them to the queue.
     * It should just be a simple loop on all item, no call to the database should be done
     * to speedup queuing.
     *
     * Faster parse entries for Producer.
     * We don't care to make check at this time. They'll be done by the consumer.
     *
     * @param array $entries
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
    public function parseEntry(array $importedEntry)
    {
        if ((!key_exists('guid', $importedEntry) || (!key_exists('id', $importedEntry))) && is_array(reset($importedEntry))) {
            $this->parseEntries($importedEntry);

            return;
        }

        if (key_exists('children', $importedEntry)) {
            $this->parseEntries($importedEntry['children']);

            return;
        }

        if (!key_exists('uri', $importedEntry) && !key_exists('url', $importedEntry)) {
            return;
        }

        $firefox = key_exists('uri', $importedEntry);

        $existingEntry = $this->em
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId(($firefox) ? $importedEntry['uri'] : $importedEntry['url'], $this->user->getId());

        if (false !== $existingEntry) {
            ++$this->skippedEntries;

            return;
        }

        $data = $this->prepareEntry($importedEntry);

        $entry = new Entry($this->user);
        $entry->setUrl($data['url']);
        $entry->setTitle($data['title']);

        // update entry with content (in case fetching failed, the given entry will be return)
        $entry = $this->fetchContent($entry, $data['url'], $data);

        if (array_key_exists('tags', $data)) {
            $this->contentProxy->assignTagsToEntry(
                $entry,
                $data['tags']
            );
        }

        $entry->setArchived($data['is_archived']);

        if (!empty($data['created_at'])) {
            $dt = new \DateTime();
            $entry->setCreatedAt($dt->setTimestamp($data['created_at'] / 1000));
        }

        $this->em->persist($entry);
        ++$this->importedEntries;

        return $entry;
    }

    /**
     * {@inheritdoc}
     */
    protected function setEntryAsRead(array $importedEntry)
    {
        $importedEntry['is_archived'] = 1;

        return $importedEntry;
    }
}
