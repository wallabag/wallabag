<?php

namespace Wallabag\Import;

class FirefoxImport extends BrowserImport
{
    protected $filepath;

    public function getName()
    {
        return 'Firefox';
    }

    public function getUrl()
    {
        return 'import_firefox';
    }

    public function getDescription()
    {
        return 'import.firefox.description';
    }

    public function validateEntry(array $importedEntry)
    {
        if (empty($importedEntry['uri'])) {
            return false;
        }

        return true;
    }

    protected function prepareEntry(array $entry = [])
    {
        $data = [
            'title' => $entry['title'],
            'html' => false,
            'url' => $entry['uri'],
            'is_archived' => (int) $this->markAsRead,
            'is_starred' => false,
            'tags' => '',
            'created_at' => substr((string) $entry['dateAdded'], 0, 10),
        ];

        if (\array_key_exists('tags', $entry) && '' !== $entry['tags']) {
            $data['tags'] = $entry['tags'];
        }

        return $data;
    }
}
