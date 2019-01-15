<?php

namespace Wallabag\ImportBundle\Import;

class FirefoxImport extends BrowserImport
{
    protected $filepath;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Firefox';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return 'import_firefox';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'import.firefox.description';
    }

    /**
     * {@inheritdoc}
     */
    public function validateEntry(array $importedEntry)
    {
        if (empty($importedEntry['uri'])) {
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
            'title' => $entry['title'],
            'html' => false,
            'url' => $entry['uri'],
            'is_archived' => (int) $this->markAsRead,
            'is_starred' => false,
            'tags' => '',
            'created_at' => substr($entry['dateAdded'], 0, 10),
        ];

        if (array_key_exists('tags', $entry) && '' !== $entry['tags']) {
            $data['tags'] = $entry['tags'];
        }

        return $data;
    }
}
