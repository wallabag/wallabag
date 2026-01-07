<?php

namespace Wallabag\Import;

class ChromeImport extends BrowserImport
{
    protected $filepath;

    public function getName()
    {
        return 'Chrome';
    }

    public function getUrl()
    {
        return 'import_chrome';
    }

    public function getDescription()
    {
        return 'import.chrome.description';
    }

    public function validateEntry(array $importedEntry)
    {
        if (empty($importedEntry['url'])) {
            return false;
        }

        return true;
    }

    protected function prepareEntry(array $entry = [])
    {
        $data = [
            'title' => $entry['name'],
            'html' => false,
            'url' => $entry['url'],
            'is_archived' => (int) $this->markAsRead,
            'is_starred' => false,
            'tags' => '',
            'created_at' => substr((string) $entry['date_added'], 0, 10),
        ];

        if (\array_key_exists('tags', $entry) && '' !== $entry['tags']) {
            $data['tags'] = $entry['tags'];
        }

        return $data;
    }
}
