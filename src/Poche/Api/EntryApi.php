<?php

namespace Poche\Api;

class EntryApi
{
    private $entryRepository;

    public function __construct($entryRepository) {
        $this->entryRepository = $entryRepository;
    }

    public function getEntries() {
        return $this->entryRepository->getEntries();
    }

    public function createEntryFromUrl($url) {

        //TODO: Fetch all what we need, fill the title, content …

        $entry = array(
            'url' => $url
        );
        return $entry;
    }

}
