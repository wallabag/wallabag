<?php

namespace Wallabag\ImportBundle\Import;

use Wallabag\CoreBundle\Entity\Entry;

class RssImport extends AbstractImport
{
    /**
     * {@inheritdoc}
     */
    public function import()
    {
        if (!$this->user) {
            $this->logger->error('RssImport: user is not defined');

            return false;
        }

        $rssFile = $this->user->getRssFile();

        if (!$rssFile) {
            $this->logger->error('RssImport: rssFile badly defined for user', ['rssFile' => $rssFile]);

            return false;
        }

        // read rss file

        if (empty($data) || empty($data['articles'])) {
            $this->logger->error('RssImport: no entries in imported file');

            return false;
        }

        if ($this->producer) {
            $this->parseEntriesForProducer($data['articles']);

            return true;
        }

        $this->parseEntries($data['articles']);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function validateEntry(array $importedEntry)
    {
        if (empty($importedEntry['url'])) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function parseEntry(array $importedEntry)
    {
        $existingEntry = $this->em
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId($importedEntry['url'], $this->user->getId());

        if (false !== $existingEntry) {
            ++$this->skippedEntries;

            return;
        }

        $data = [
            'title' => $importedEntry['title'],
            'url' => $importedEntry['url'],
            'created_at' => $importedEntry['date'],
            'html' => false,
        ];

        $entry = new Entry($this->user);
        $entry->setUrl($data['url']);
        $entry->setTitle($data['title']);

        // update entry with content (in case fetching failed, the given entry will be return)
        $this->fetchContent($entry, $data['url'], $data);

        if (!empty($data['created_at'])) {
            $entry->setCreatedAt(new \DateTime($data['created_at']));
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
        return $importedEntry;
    }
}
