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
            // Chrome stores microseconds since 1601-01-01
            'created_at' => empty($entry['date_added']) ? '' : (string) (intdiv((int) $entry['date_added'], 1000000) - 11644473600),
        ];

        if (\array_key_exists('tags', $entry) && '' !== $entry['tags']) {
            $data['tags'] = $entry['tags'];
        }

        return $data;
    }
}
