<?php

namespace Wallabag\ImportBundle\Import;

class WallabagV2Import extends WallabagImport
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'wallabag v2';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return 'import_wallabag_v2';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'import.wallabag_v2.description';
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareEntry($entry = [], $markAsRead = false)
    {
        return [
            'html' => $entry['content'],
            'content_type' => $entry['mimetype'],
            'is_archived' => ($entry['is_archived'] || $markAsRead),
        ] + $entry;
    }
}
