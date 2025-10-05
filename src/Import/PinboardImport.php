<?php

namespace Wallabag\Import;

use Wallabag\Entity\Entry;

class PinboardImport extends AbstractImport
{
    private $filepath;

    public function getName()
    {
        return 'Pinboard';
    }

    public function getUrl()
    {
        return 'import_pinboard';
    }

    public function getDescription()
    {
        return 'import.pinboard.description';
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
            $this->logger->error('PinboardImport: user is not defined');

            return false;
        }

        if (!file_exists($this->filepath) || !is_readable($this->filepath)) {
            $this->logger->error('PinboardImport: unable to read file', ['filepath' => $this->filepath]);

            return false;
        }

        $data = json_decode(file_get_contents($this->filepath), true);

        if (empty($data)) {
            $this->logger->error('PinboardImport: no entries in imported file');

            return false;
        }

        if ($this->producer) {
            $this->parseEntriesForProducer($data);

            return true;
        }

        $this->parseEntries($data);

        return true;
    }

    public function validateEntry(array $importedEntry)
    {
        if (empty($importedEntry['href'])) {
            return false;
        }

        return true;
    }

    public function parseEntry(array $importedEntry)
    {
        $existingEntry = $this->em
            ->getRepository(Entry::class)
            ->findByUrlAndUserId($importedEntry['href'], $this->user->getId());

        if (false !== $existingEntry) {
            ++$this->skippedEntries;

            return null;
        }

        $data = [
            'title' => $importedEntry['description'],
            'url' => $importedEntry['href'],
            'is_archived' => ('no' === $importedEntry['toread']) || $this->markAsRead,
            'is_starred' => false,
            'created_at' => $importedEntry['time'],
            'tags' => array_filter(explode(' ', (string) $importedEntry['tags'])),
        ];

        $entry = new Entry($this->user);
        $entry->setUrl($data['url']);
        $entry->setTitle($data['title']);

        // update entry with content (in case fetching failed, the given entry will be return)
        $this->fetchContent($entry, $data['url'], $data);

        if (!empty($data['tags'])) {
            $this->tagsAssigner->assignTagsToEntry(
                $entry,
                $data['tags'],
                $this->em->getUnitOfWork()->getScheduledEntityInsertions()
            );
        }

        $entry->updateArchived($data['is_archived']);
        $entry->setStarred($data['is_starred']);
        $entry->setCreatedAt(new \DateTime($data['created_at']));

        $this->em->persist($entry);
        ++$this->importedEntries;

        return $entry;
    }

    protected function setEntryAsRead(array $importedEntry)
    {
        $importedEntry['toread'] = 'no';

        return $importedEntry;
    }
}
