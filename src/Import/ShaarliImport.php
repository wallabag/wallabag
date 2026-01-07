<?php

namespace Wallabag\Import;

class ShaarliImport extends HtmlImport
{
    protected $filepath;

    public function getName()
    {
        return 'Shaarli';
    }

    public function getUrl()
    {
        return 'import_shaarli';
    }

    public function getDescription()
    {
        return 'import.shaarli.description';
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
            'title' => '',
            'html' => false,
            'url' => $entry['url'],
            'is_archived' => (int) $this->markAsRead,
            'is_starred' => false,
            'tags' => '',
            'created_at' => $entry['created_at'],
        ];

        if (\array_key_exists('tags', $entry) && '' !== $entry['tags']) {
            $data['tags'] = $entry['tags'];
        }

        return $data;
    }
}
