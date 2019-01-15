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
    protected function prepareEntry(array $entry = [])
    {
        $data = [
            'title' => $entry['name'],
            'html' => false,
            'url' => $entry['url'],
            'is_archived' => (int) $this->markAsRead,
            'is_starred' => false,
            'tags' => '',
            'created_at' => substr($entry['date_added'], 0, 10),
        ];

        if (array_key_exists('tags', $entry) && '' !== $entry['tags']) {
            $data['tags'] = $entry['tags'];
        }

        return $data;
    }
}
