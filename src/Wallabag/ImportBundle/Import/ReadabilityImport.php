<?php

namespace Wallabag\ImportBundle\Import;

use Wallabag\CoreBundle\Entity\Entry;

class ReadabilityImport extends AbstractImport
{
    private $filepath;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Readability';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return 'import_readability';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'import.readability.description';
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
     * {@inheritdoc}
     */
    public function import()
    {
        if (!$this->user) {
            $this->logger->error('ReadabilityImport: user is not defined');

            return false;
        }

        if (!file_exists($this->filepath) || !is_readable($this->filepath)) {
            $this->logger->error('ReadabilityImport: unable to read file', ['filepath' => $this->filepath]);

            return false;
        }

        $data = json_decode(file_get_contents($this->filepath), true);

        if (empty($data) || empty($data['bookmarks'])) {
            $this->logger->error('ReadabilityImport: no entries in imported file');

            return false;
        }

        if ($this->producer) {
            $this->parseEntriesForProducer($data['bookmarks']);

            return true;
        }

        $this->parseEntries($data['bookmarks']);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function validateEntry(array $importedEntry)
    {
        if (empty($importedEntry['article__url'])) {
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
            ->findByUrlAndUserId($importedEntry['article__url'], $this->user->getId());

        if (false !== $existingEntry) {
            ++$this->skippedEntries;

            return;
        }

        $data = [
            'title' => $importedEntry['article__title'],
            'url' => $importedEntry['article__url'],
            'is_archived' => $importedEntry['archive'] || $this->markAsRead,
            'is_starred' => $importedEntry['favorite'],
            'created_at' => $importedEntry['date_added'],
            'html' => false,
        ];

        $entry = new Entry($this->user);
        $entry->setUrl($data['url']);
        $entry->setTitle($data['title']);

        // update entry with content (in case fetching failed, the given entry will be return)
        $this->fetchContent($entry, $data['url'], $data);

        $entry->setArchived($data['is_archived']);
        $entry->setStarred($data['is_starred']);
        $entry->setCreatedAt(new \DateTime($data['created_at']));

        $this->em->persist($entry);
        ++$this->importedEntries;

        return $entry;
    }

    /**
     * {@inheritdoc}
     */
    protected function setEntryAsRead(array $importedEntry)
    {
        $importedEntry['archive'] = 1;

        return $importedEntry;
    }
}
