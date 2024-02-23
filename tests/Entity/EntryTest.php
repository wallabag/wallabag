<?php

namespace Tests\Wallabag\Entity;

use Tests\Wallabag\WallabagCoreTestCase;
use Wallabag\Entity\Entry;

class EntryTest extends WallabagCoreTestCase
{
    public function testGetLanguage()
    {
        $this->logInAs('admin');
        $entry = new Entry($this->getLoggedInUser());
        $languages = [
            'en_GB' => 'en-GB',
            'en_US' => 'en-US',
            'en-gb' => 'en-GB',
            'en-US' => 'en-US',
            'fr' => 'fr',
            'fr_FR' => 'fr-FR',
            'ja' => 'ja',
        ];
        foreach ($languages as $entryLang => $lang) {
            $entry->setLanguage($entryLang);
            $this->assertSame($lang, $entry->getHTMLLanguage());
        }
    }
}
