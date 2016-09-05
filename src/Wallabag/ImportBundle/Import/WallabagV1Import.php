<?php

namespace Wallabag\ImportBundle\Import;

class WallabagV1Import extends WallabagImport
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'wallabag v1';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return 'import_wallabag_v1';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'import.wallabag_v1.description';
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareEntry($entry = [])
    {
        $data = [
            'title' => $entry['title'],
            'html' => $entry['content'],
            'url' => $entry['url'],
            'content_type' => '',
            'language' => '',
            'is_archived' => $entry['is_read'] || $this->markAsRead,
            'is_starred' => $entry['is_fav'],
            'tags' => '',
        ];

        // force content to be refreshed in case on bad fetch in the v1 installation
        if (in_array($entry['title'], $this->untitled)) {
            $data['title'] = '';
            $data['html'] = '';
        }

        if (array_key_exists('tags', $entry) && $entry['tags'] != '') {
            $data['tags'] = $entry['tags'];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function setEntryAsRead(array $importedEntry)
    {
        $importedEntry['is_read'] = 1;

        return $importedEntry;
    }
}
