<?php

namespace Wallabag\ImportBundle\Import;

class ChromeImport extends BrowserImport
{
    protected $filepath;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Chrome';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return 'import_chrome';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'import.chrome.description';
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareEntry($entry = [])
    {
        $data = [
          'title' => $entry['name'],
          'html' => '',
          'url' => $entry['url'],
          'is_archived' => $this->markAsRead,
          'tags' => '',
          'created_at' => $entry['date_added'],
        ];

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
        $importedEntry['is_archived'] = 1;

        return $importedEntry;
    }
}
