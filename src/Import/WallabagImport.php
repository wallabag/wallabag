<?php

namespace Wallabag\Import;

use Wallabag\Entity\Entry;

abstract class WallabagImport extends AbstractImport
{
    protected $filepath;
    // untitled in all languages from v1
    protected $untitled = [
        'Untitled',
        'Sans titre',
        'podle nadpisu',
        'Sin título',
        'با عنوان',
        'per titolo',
        'Sem título',
        'Без названия',
        'po naslovu',
        'Без назви',
        'No title found',
        '',
    ];

    abstract public function getName();

    abstract public function getUrl();

    abstract public function getDescription();

    public function import()
    {
        if (!$this->user) {
            $this->logger->error('WallabagImport: user is not defined');

            return false;
        }

        if (!file_exists($this->filepath) || !is_readable($this->filepath)) {
            $this->logger->error('WallabagImport: unable to read file', ['filepath' => $this->filepath]);

            return false;
        }

        $data = json_decode(file_get_contents($this->filepath), true);

        if (empty($data)) {
            $this->logger->error('WallabagImport: no entries in imported file');

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

    public function parseEntry(array $importedEntry)
    {
        $existingEntry = $this->em
            ->getRepository(Entry::class)
            ->findByUrlAndUserId($importedEntry['url'], $this->user->getId());

        if (false !== $existingEntry) {
            ++$this->skippedEntries;

            return null;
        }

        $data = $this->prepareEntry($importedEntry);

        $entry = new Entry($this->user);
        $entry->setUrl($data['url']);
        $entry->setTitle($data['title']);

        if (\array_key_exists('is_parsed', $data)) {
            $entry->setNotParsed(true);
        }

        // update entry with content (in case fetching failed, the given entry will be return)
        $this->fetchContent($entry, $data['url'], $data);

        if (\array_key_exists('tags', $data)) {
            $this->tagsAssigner->assignTagsToEntry(
                $entry,
                $data['tags'],
                $this->em->getUnitOfWork()->getScheduledEntityInsertions()
            );
        }

        if (isset($importedEntry['preview_picture'])) {
            $entry->setPreviewPicture($importedEntry['preview_picture']);
        }

        $entry->updateArchived($data['is_archived']);
        $entry->setStarred($data['is_starred']);

        if (!empty($data['created_at'])) {
            $entry->setCreatedAt(new \DateTime($data['created_at']));
        }

        $this->em->persist($entry);
        ++$this->importedEntries;

        return $entry;
    }

    /**
     * This should return a cleaned array for a given entry to be given to `updateEntry`.
     *
     * @param array $entry Data from the imported file
     *
     * @return array
     */
    abstract protected function prepareEntry($entry = []);
}
