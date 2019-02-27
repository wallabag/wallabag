<?php

namespace Wallabag\ImportBundle\Import;

class WallabagV1Import extends WallabagImport
{
    protected $fetchingErrorMessage;
    protected $fetchingErrorMessageTitle;

    public function __construct($em, $contentProxy, $tagsAssigner, $eventDispatcher, $fetchingErrorMessageTitle, $fetchingErrorMessage)
    {
        $this->fetchingErrorMessageTitle = $fetchingErrorMessageTitle;
        $this->fetchingErrorMessage = $fetchingErrorMessage;

        parent::__construct($em, $contentProxy, $tagsAssigner, $eventDispatcher);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'wallabag v1';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return 'import_wallabag_v1';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'import.wallabag_v1.description';
    }

    /**
     * {@inheritdoc}
     */
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
        }

        if (\array_key_exists('tags', $entry) && '' !== $entry['tags']) {
            $data['tags'] = $entry['tags'];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function setEntryAsRead(array $importedEntry)
    {
        $importedEntry['is_read'] = 1;

        return $importedEntry;
    }
}
