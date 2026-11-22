<?php

namespace Wallabag\Import;

use Wallabag\Entity\Entry;

class PocketCsvImport extends AbstractFileImport
{
    protected $filepath;

    public function getName()
    {
        return 'Pocket CSV';
    }

    public function getUrl()
    {
        return 'import_pocket_csv';
    }

    public function getDescription()
    {
        return 'import.pocket_csv.description';
    }

    public function setFilepath($filepath): static
    {
        $this->filepath = $filepath;

        return $this;
    }

    public function validateEntry(array $importedEntry)
    {
        if (empty($importedEntry['url'])) {
            return false;
        }

        return true;
    }

    public function import()
    {
        if (!$this->user) {
            $this->logger->error('Pocket CSV Import: user is not defined');

            return false;
        }

        if (!file_exists($this->filepath) || !is_readable($this->filepath)) {
            $this->logger->error('Pocket CSV Import: unable to read file', ['filepath' => $this->filepath]);

            return false;
        }

        $entries = [];
        // Pocket's later exports add a cursor column before tags, so read columns by header name
        $columns = ['title' => 0, 'url' => 1, 'time_added' => 2, 'tags' => 3, 'status' => 4];
        $firstRow = true;
        $handle = fopen($this->filepath, 'r');

        while (false !== ($data = fgetcsv($handle, 10240, ',', '"', ''))) {
            if ($firstRow) {
                $firstRow = false;
                $data[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $data[0]);
                $header = array_flip(array_map(static fn ($name) => strtolower(trim((string) $name)), $data));

                if (isset($header['title'], $header['url'], $header['time_added'], $header['tags'], $header['status'])) {
                    $columns = $header;

                    continue;
                }
            }

            $value = static fn (string $column) => isset($columns[$column]) ? ($data[$columns[$column]] ?? '') : '';
            $entries[] = [
                'url' => $value('url'),
                'title' => $value('title'),
                'is_archived' => 'archive' === $value('status'),
                'created_at' => $value('time_added'),
                'tags' => $value('tags'),
            ];
        }
        fclose($handle);

        if (empty($entries)) {
            $this->logger->error('PocketCsvImport: no entries in imported file');

            return false;
        }

        if ($this->producer) {
            $this->parseEntriesForProducer($entries);

            return true;
        }

        $this->parseEntries($entries);

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
            $tags = str_replace('|', ',', $importedEntry['tags']);
            $this->tagsAssigner->assignTagsToEntry(
                $entry,
                $tags,
                $this->em->getUnitOfWork()->getScheduledEntityInsertions()
            );
        }

        $entry->updateArchived($importedEntry['is_archived']);
        $entry->setCreatedAt(\DateTime::createFromFormat('U', $importedEntry['created_at']));

        $this->em->persist($entry);
        ++$this->importedEntries;

        return $entry;
    }

    protected function setEntryAsRead(array $importedEntry)
    {
        $importedEntry['is_archived'] = 'archive';

        return $importedEntry;
    }
}
