<?php

namespace Poche\Api;

class EntryApi
{
    private $entryRepository;

    public function __construct($entryRepository, $contentApi) {
        $this->entryRepository = $entryRepository;
        $this->contentApi = $contentApi;
    }

    public function getEntries() {
        return $this->entryRepository->getEntries();
    }

    public function getEntryById($id) {
        return $this->entryRepository->getEntryById($id);
    }

    public function createEntryFromUrl($url) {

        //TODO: Fetch all what we need, fill the title, content …

        $content = $this->contentApi->fetchUrl($url);

        $entry = array(
            'url' => $url,
            'title' => $content['title'],
            'content' => $content['content']
        );
        return $entry;
    }


    public function createAndSaveEntryFromUrl($url) {

        $entry = $this->createEntryFromUrl($url);
        return $this->entryRepository->saveEntry($entry);

    }
}
