<?php

namespace Wallabag\ImportBundle\Import;

class WallabagV2Import extends WallabagImport
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'wallabag v2';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return 'import_wallabag_v2';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'import.wallabag_v2.description';
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareEntry($entry = [])
    {
        return [
            'html' => $entry['content'],
            'content_type' => $entry['mimetype'],
            'is_archived' => ($entry['is_archived'] || $this->markAsRead),
        ] + $entry;
    }

    protected function parseEntriesForProducer($entries)
    {
        foreach ($entries as $importedEntry) {
            // set userId for the producer (it won't know which user is connected)
            $importedEntry['userId'] = $this->user->getId();

            if ($this->markAsRead) {
                $importedEntry['is_archived'] = 1;
            }

            ++$this->importedEntries;

            $this->producer->publish(json_encode($importedEntry));
        }
    }
}
