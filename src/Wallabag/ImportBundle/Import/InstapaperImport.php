<?php

namespace Wallabag\ImportBundle\Import;

use Wallabag\CoreBundle\Entity\Entry;

class InstapaperImport extends AbstractImport
{
    private $filepath;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Instapaper';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return 'import_instapaper';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'import.instapaper.description';
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
            $this->logger->error('InstapaperImport: user is not defined');

            return false;
        }

        if (!file_exists($this->filepath) || !is_readable($this->filepath)) {
            $this->logger->error('InstapaperImport: unable to read file', ['filepath' => $this->filepath]);

            return false;
        }

        $entries = [];
        $handle = fopen($this->filepath, 'rb');
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
                'status' => $data[3],
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

        if ($this->producer) {
            $this->parseEntriesForProducer($entries);

            return true;
        }

        $this->parseEntries($entries);

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

        $entry->setArchived($importedEntry['is_archived']);
        $entry->setStarred($importedEntry['is_starred']);

        $this->em->persist($entry);
        ++$this->importedEntries;

        return $entry;
    }

    /**
     * {@inheritdoc}
     */
    protected function setEntryAsRead(array $importedEntry)
    {
        $importedEntry['is_archived'] = 1;

        return $importedEntry;
    }
}
