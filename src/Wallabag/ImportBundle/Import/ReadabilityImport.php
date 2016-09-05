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
    public function getSummary()
    {
        return [
            'skipped' => $this->skippedEntries,
            'imported' => $this->importedEntries,
        ];
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
            return false;
        }

        if ($this->producer) {
            $this->parseEntriesForProducer($data['bookmarks']);

            return true;
        }

        $this->parseEntries($data['bookmarks']);

        return true;
    }

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
            'content_type' => '',
            'language' => '',
            'is_archived' => $importedEntry['archive'] || $this->markAsRead,
            'is_starred' => $importedEntry['favorite'],
        ];

        $entry = $this->fetchContent(
            new Entry($this->user),
            $data['url'],
            $data
        );

        // jump to next entry in case of problem while getting content
        if (false === $entry) {
            ++$this->skippedEntries;

            return;
        }

        $entry->setArchived($data['is_archived']);
        $entry->setStarred($data['is_starred']);

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
