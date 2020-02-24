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
            'headers' => [
                'content-type' => $entry['mimetype'],
            ],
            'is_archived' => (bool) ($entry['is_archived'] || $this->markAsRead),
            'is_starred' => (bool) $entry['is_starred'],
        ] + $entry;
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
