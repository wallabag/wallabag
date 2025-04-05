<?php

namespace Wallabag\Import;

use Wallabag\Entity\Entry;

class InstapaperImport extends AbstractImport
{
    private $filepath;

    public function getName()
    {
        return 'Instapaper';
    }

    public function getUrl()
    {
        return 'import_instapaper';
    }

    public function getDescription()
    {
        return 'import.instapaper.description';
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

    public function import()
    {
        if (!$this->user) {
            $this->logger->error('InstapaperImport: user is not defined');

            return false;
        }

        if (!file_exists($this->filepath) || !is_readable($this->filepath)) {
            $this->logger->error('InstapaperImport: unable to read file', ['filepath' => $this->filepath]);

            return false;
        }

        $entries = [];
        $handle = fopen($this->filepath, 'r');
        while (false !== ($data = fgetcsv($handle, 10240))) {
            if ('URL' === $data[0]) {
                continue;
            }

            // last element in the csv is the folder where the content belong
            // BUT it can also be the status (since status = folder in Instapaper)
            // and we don't want archive, unread & starred to become a tag
            $tags = null;
            if (false === \in_array($data[3], ['Archive', 'Unread', 'Starred'], true)) {
                $tags = [$data[3]];
            }

            $entries[] = [
                'url' => $data[0],
                'title' => $data[1],
                'is_archived' => 'Archive' === $data[3] || 'Starred' === $data[3],
                'is_starred' => 'Starred' === $data[3],
                'html' => false,
                'tags' => $tags,
            ];
        }
        fclose($handle);

        if (empty($entries)) {
            $this->logger->error('InstapaperImport: no entries in imported file');

            return false;
        }

        // most recent articles are first, which means we should create them at the end so they will show up first
        // as Instapaper doesn't export the creation date of the article
        $entries = array_reverse($entries);

        if ($this->producer) {
            $this->parseEntriesForProducer($entries);

            return true;
        }

        $this->parseEntries($entries);

        return true;
    }

    public function validateEntry(array $importedEntry)
    {
        if (empty($importedEntry['url'])) {
            return false;
        }

        return true;
    }

    public function parseEntry(array $importedEntry)
    {
        $existingEntry = $this->em
            ->getRepository(Entry::class)
            ->findByUrlAndUserId($importedEntry['url'], $this->user->getId());

        if (false !== $existingEntry) {
            ++$this->skippedEntries;

            return null;
        }

        $entry = new Entry($this->user);
        $entry->setUrl($importedEntry['url']);
        $entry->setTitle($importedEntry['title']);

        // update entry with content (in case fetching failed, the given entry will be return)
        $this->fetchContent($entry, $importedEntry['url'], $importedEntry);

        if (!empty($importedEntry['tags'])) {
            $this->tagsAssigner->assignTagsToEntry(
                $entry,
                $importedEntry['tags'],
                $this->em->getUnitOfWork()->getScheduledEntityInsertions()
            );
        }

        $entry->updateArchived($importedEntry['is_archived']);
        $entry->setStarred($importedEntry['is_starred']);

        $this->em->persist($entry);
        ++$this->importedEntries;

        return $entry;
    }

    protected function setEntryAsRead(array $importedEntry)
    {
        $importedEntry['is_archived'] = 1;

        return $importedEntry;
    }
}
