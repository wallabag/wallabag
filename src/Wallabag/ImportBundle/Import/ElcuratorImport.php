<?php

namespace Wallabag\ImportBundle\Import;

class ElcuratorImport extends WallabagImport
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'elcurator';
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl()
    {
        return 'import_elcurator';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'import.elcurator.description';
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareEntry($entry = [])
    {
        return [
            'url' => $entry['url'],
            'title' => $entry['title'],
            'created_at' => $entry['created_at'],
            'is_archived' => 0,
            'is_starred' => $entry['is_saved'],
        ] + $entry;
    }

    /**
     * {@inheritdoc}
     */
    protected function setEntryAsRead(array $importedEntry)
    {
        $importedEntry['is_archived'] = 1;

        return $importedEntry;
    }
}
