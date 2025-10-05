<?php

namespace Wallabag\Import;

use Symfony\Component\DomCrawler\Crawler;

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

        $crawler = new Crawler(file_get_contents($this->filepath));

        $hrefs = $crawler->filterXPath('//a');

        if (0 === $hrefs->count()) {
            $this->logger->error('Pocket HTML: no entries in imported file');

            return false;
        }

        $entries = $hrefs->each(fn (Crawler $node) => [
            'url' => $node->attr('href'),
            'tags' => $node->attr('tags'),
            'created_at' => $node->attr('time_added'),
        ]);

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
