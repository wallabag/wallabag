<?php

namespace Wallabag\Import;

class ElcuratorImport extends WallabagImport
{
    public function getName()
    {
        return 'elcurator';
    }

    public function getUrl()
    {
        return 'import_elcurator';
    }

    public function getDescription()
    {
        return 'import.elcurator.description';
    }

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

    protected function setEntryAsRead(array $importedEntry)
    {
        $importedEntry['is_archived'] = 1;

        return $importedEntry;
    }
}
