<?php

namespace Wallabag\Import;

abstract class AbstractFileImport extends AbstractImport implements ImportInterface
{
    protected function prepareEntryForImport(array $importedEntry): array
    {
        return $this->markAsRead ? $this->setEntryAsRead($importedEntry) : $importedEntry;
    }

    /** @return array */
    abstract protected function setEntryAsRead(array $importedEntry);
}
