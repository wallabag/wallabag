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
            'created_at' => $this->convertChromeDateToUnixTimestamp($entry['date_added'] ?? null),
        ];

        if (\array_key_exists('tags', $entry) && '' !== $entry['tags']) {
            $data['tags'] = $entry['tags'];
        }

        return $data;
    }

    /**
     * Converts Chrome's date_added (microseconds since 1601-01-01) to a Unix timestamp.
     *
     * @see https://chromium.googlesource.com/chromium/src/+/main/components/bookmarks/browser/bookmark_codec.cc#183
     */
    private function convertChromeDateToUnixTimestamp($dateAdded): string
    {
        if (empty($dateAdded)) {
            return '';
        }

        return (string) (intdiv((int) $dateAdded, 1_000_000) - 11_644_473_600);
    }
}
