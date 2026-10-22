<?php

namespace Wallabag\ImportBundle\Import;

class PocketHtmlImport extends HtmlImport
{
    protected $filepath;

    public function getName()
    {
        return 'Pocket HTML';
    }

    public function getUrl()
    {
        return 'import_pocket_html';
    }

    public function getDescription()
    {
        return 'import.pocket_html.description';
    }

    public function validateEntry(array $importedEntry)
    {
        if (empty($importedEntry['url'])) {
            return false;
        }

        return true;
    }

    public function import()
    {
        if (!$this->user) {
            $this->logger->error('Pocket HTML Import: user is not defined');

            return false;
        }

        if (!file_exists($this->filepath) || !is_readable($this->filepath)) {
            $this->logger->error('Pocket HTML Import: unable to read file', ['filepath' => $this->filepath]);

            return false;
        }

        $html = new \DOMDocument();

        libxml_use_internal_errors(true);
        $html->loadHTMLFile($this->filepath);
        $hrefs = $html->getElementsByTagName('a');
        libxml_use_internal_errors(false);

        if (0 === $hrefs->length) {
            $this->logger->error('Pocket HTML: no entries in imported file');

            return false;
        }

        $entries = [];
        foreach ($hrefs as $href) {
            $entry = [];
            $entry['url'] = $href->getAttribute('href');
            $entry['tags'] = $href->getAttribute('tags');
            $entry['created_at'] = $href->getAttribute('time_added');
            $entries[] = $entry;
        }

        if ($this->producer) {
            $this->parseEntriesForProducer($entries);

            return true;
        }

        $this->parseEntries($entries);

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
