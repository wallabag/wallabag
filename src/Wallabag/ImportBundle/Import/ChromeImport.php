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
    protected function prepareEntry(array $entry = [])
    {
        $data = [
            'title' => $entry['name'],
            'html' => '',
            'url' => $entry['url'],
            'is_archived' => $this->markAsRead,
            'tags' => '',
            'created_at' => substr($entry['date_added'], 0, 10),
        ];

        if (array_key_exists('tags', $entry) && $entry['tags'] != '') {
            $data['tags'] = $entry['tags'];
        }

        return $data;
    }
}
