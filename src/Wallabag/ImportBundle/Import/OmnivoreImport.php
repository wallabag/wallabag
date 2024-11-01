<?php

namespace Wallabag\ImportBundle\Import;

use Wallabag\CoreBundle\Entity\Entry;

class OmnivoreImport extends AbstractImport
{
    private $filepath;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Omnivore';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return 'import_omnivore';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'import.omnivore.description';
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
            $this->logger->error('OmnivoreImport: user is not defined');

            return false;
        }

        if (!file_exists($this->filepath) || !is_readable($this->filepath)) {
            $this->logger->error('OmnivoreImport: unable to read file', ['filepath' => $this->filepath]);

            return false;
        }

        $data = json_decode(file_get_contents($this->filepath), true);

        if (empty($data)) {
            $this->logger->error('OmnivoreImport: no entries in imported file');

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
            ->getRepository(Entry::class)
            ->findByUrlAndUserId($importedEntry['url'], $this->user->getId());

        if (false !== $existingEntry) {
            ++$this->skippedEntries;

            return;
        }

        $data = [
            'title' => $importedEntry['title'],
            'url' => $importedEntry['url'],
            'is_archived' => ('Archived' === $importedEntry['state']) || $this->markAsRead,
            'is_starred' => false,
            'created_at' => $importedEntry['savedAt'],
            'tags' => $importedEntry['labels'],
            'published_by' => [$importedEntry['author']],
            'published_at' => $importedEntry['publishedAt'],
            'preview_picture' => $importedEntry['thumbnail'],
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
        $entry->setCreatedAt(\DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $data['created_at']));
        if (null !== $data['published_at']) {
            $entry->setPublishedAt(\DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $data['published_at']));
        }
        $entry->setPublishedBy($data['published_by']);
        $entry->setPreviewPicture($data['preview_picture']);

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
