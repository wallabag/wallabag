<?php

namespace Wallabag\Import;

use Wallabag\Entity\Entry;

/**
 * Import a plain text file containing one URL per line.
 *
 * Empty lines and lines starting with "#" are ignored, as well as lines which aren't HTTP(S) URLs.
 */
class UrlListImport extends AbstractFileImport
{
    protected $filepath;

    public function getName()
    {
        return 'URL list';
    }

    public function getUrl()
    {
        return 'import_url_list';
    }

    public function getDescription()
    {
        return 'import.url_list.description';
    }

    public function setFilepath($filepath): static
    {
        $this->filepath = $filepath;

        return $this;
    }

    public function validateEntry(array $importedEntry)
    {
        if (empty($importedEntry['url']) || !\is_string($importedEntry['url'])) {
            return false;
        }

        $scheme = parse_url($importedEntry['url'], \PHP_URL_SCHEME);
        $host = parse_url($importedEntry['url'], \PHP_URL_HOST);

        return \is_string($scheme) && \in_array(strtolower($scheme), ['http', 'https'], true) && !empty($host);
    }

    public function import()
    {
        if (!$this->user) {
            $this->logger->error('URL list Import: user is not defined');

            return false;
        }

        if (!file_exists($this->filepath) || !is_readable($this->filepath)) {
            $this->logger->error('URL list Import: unable to read file', ['filepath' => $this->filepath]);

            return false;
        }

        $lines = file($this->filepath, \FILE_IGNORE_NEW_LINES);

        if (false === $lines) {
            $this->logger->error('URL list Import: unable to read file', ['filepath' => $this->filepath]);

            return false;
        }

        $entries = [];
        foreach ($lines as $index => $line) {
            if (0 === $index) {
                // remove an eventual UTF-8 BOM
                $line = preg_replace('/^\xEF\xBB\xBF/', '', $line);
            }

            $url = trim($line);
            $entry = [
                'url' => $url,
                'is_archived' => false,
            ];

            // empty lines, comments and anything else than a web address are ignored
            // an url listed more than once is only imported once
            if (!$this->validateEntry($entry) || isset($entries[$url])) {
                continue;
            }

            $entries[$url] = $entry;
        }

        if (empty($entries)) {
            $this->logger->error('URL list Import: no URL found in imported file');

            return false;
        }

        $entries = array_values($entries);

        if ($this->producer) {
            $this->parseEntriesForProducer($entries);

            return true;
        }

        $this->parseEntries($entries);

        return true;
    }

    public function parseEntry(array $importedEntry)
    {
        $existingEntry = $this->em
            ->getRepository(Entry::class)
            ->findByUrlAndUserId($importedEntry['url'], $this->user->getId());

        if (false !== $existingEntry) {
            ++$this->skippedEntries;

            return null;
        }

        $entry = new Entry($this->user);
        $entry->setUrl($importedEntry['url']);

        // fetch the content (in case of failure, the entry is still saved with its url)
        $this->fetchContent($entry, $importedEntry['url']);

        $entry->updateArchived(!empty($importedEntry['is_archived']));

        $this->em->persist($entry);
        ++$this->importedEntries;

        return $entry;
    }

    protected function setEntryAsRead(array $importedEntry)
    {
        $importedEntry['is_archived'] = true;

        return $importedEntry;
    }
}
