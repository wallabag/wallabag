<?php

namespace Wallabag\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;

class EntryTest extends TestCase
{
    public function testGetLanguage()
    {
        $entry = new Entry(new User());
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
