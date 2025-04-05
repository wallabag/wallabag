<?php

namespace Wallabag\Import;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Wallabag\Helper\ContentProxy;
use Wallabag\Helper\TagsAssigner;

class WallabagV1Import extends WallabagImport
{
    public function __construct(
        EntityManagerInterface $em,
        ContentProxy $contentProxy,
        TagsAssigner $tagsAssigner,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger,
        protected $fetchingErrorMessageTitle,
        protected $fetchingErrorMessage,
    ) {
        parent::__construct($em, $contentProxy, $tagsAssigner, $eventDispatcher, $logger);
    }

    public function getName()
    {
        return 'wallabag v1';
    }

    public function getUrl()
    {
        return 'import_wallabag_v1';
    }

    public function getDescription()
    {
        return 'import.wallabag_v1.description';
    }

    protected function prepareEntry($entry = [])
    {
        $data = [
            'title' => $entry['title'],
            'html' => $entry['content'],
            'url' => $entry['url'],
            'is_archived' => $entry['is_read'] || $this->markAsRead,
            'is_starred' => $entry['is_fav'],
            'tags' => '',
            'created_at' => '',
        ];

        // In case of a bad fetch in v1, replace title and content with v2 error strings
        // If fetching fails again, they will get this instead of the v1 strings
        if (\in_array($entry['title'], $this->untitled, true)) {
            $data['title'] = $this->fetchingErrorMessageTitle;
            $data['html'] = $this->fetchingErrorMessage;
            $entry['is_not_parsed'] = 1;
        }

        if (\array_key_exists('tags', $entry) && '' !== $entry['tags']) {
            $data['tags'] = $entry['tags'];
        }

        return $data;
    }

    protected function setEntryAsRead(array $importedEntry)
    {
        $importedEntry['is_read'] = 1;

        return $importedEntry;
    }
}
