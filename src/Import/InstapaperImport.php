<?php

namespace Wallabag\Import;

use Wallabag\Entity\Entry;
use Wallabag\Entity\User;

class InstapaperImport extends AbstractImport
{
    private $filepath;
    private $userId;

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

        // Store user ID to reattach user entity after em->clear()
        if ($this->user->getId()) {
            $this->userId = $this->user->getId();
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

            // Instapaper has two CSV export formats:
            // Old format (4 columns): URL,Title,Selection,Folder
            // New format (6 columns, as of 11/20/25): URL,Title,Selection,Folder,Timestamp,Tags
            // where Tags is a JSON array (e.g. '["programming", "go", "security"]')

            $hasNewFormat = isset($data[5]) || isset($data[4]);
            $tags = null;

            if ($hasNewFormat && !empty($data[5])) {
                // New format: Tags column exists and contains JSON array
                $parsedTags = json_decode($data[5], true);
                if (\is_array($parsedTags) && !empty($parsedTags)) {
                    $tags = $parsedTags;
                }
            } elseif (!$hasNewFormat && false === \in_array($data[3], ['Archive', 'Unread', 'Starred'], true)) {
                // Old format: Folder column becomes a tag unless it's a status folder
                $tags = [$data[3]];
            }

            $entries[] = [
                'url' => $data[0],
                'title' => $data[1],
                'is_archived' => 'Archive' === $data[3] || 'Starred' === $data[3],
                'is_starred' => 'Starred' === $data[3],
                'html' => false,
                'created_at' => $hasNewFormat && isset($data[4]) ? $data[4] : null,
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
        // Reattach user entity if it was detached by em->clear() in AbstractImport
        if ($this->userId && !$this->em->contains($this->user)) {
            $this->user = $this->em->getReference(User::class, $this->userId);
        }

        $existingEntry = $this->em
            ->getRepository(Entry::class)
            ->findByUrlAndUserId($importedEntry['url'], $this->userId ?: $this->user->getId());

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

        if (!empty($importedEntry['created_at'])) {
            $entry->setCreatedAt(\DateTime::createFromFormat('U', $importedEntry['created_at']));
        }

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
