<?php

namespace Wallabag\ImportBundle\Import;

use Wallabag\CoreBundle\Entity\Entry;

class PinboardImport extends AbstractImport
{
    private $filepath;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Pinboard';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return 'import_pinboard';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'import.pinboard.description';
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

    /**
     * {@inheritdoc}
     */
    public function validateEntry(array $importedEntry)
    {
        if (empty($importedEntry['href'])) {
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
            ->findByUrlAndUserId($importedEntry['href'], $this->user->getId());

        if (false !== $existingEntry) {
            ++$this->skippedEntries;

            return;
        }

        $data = [
            'title' => $importedEntry['description'],
            'url' => $importedEntry['href'],
            'is_archived' => ('no' === $importedEntry['toread']) || $this->markAsRead,
            'is_starred' => false,
            'created_at' => $importedEntry['time'],
            'tags' => explode(' ', $importedEntry['tags']),
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
        $importedEntry['toread'] = 'no';

        return $importedEntry;
    }
}
