<?php

namespace Tests\Wallabag\CoreBundle\Tools;

use Symfony\Component\Finder\Finder;
use Wallabag\CoreBundle\Tools\Utils;

class UtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider examples
     */
    public function testCorrectWordsCountForDifferentLanguages($text, $expectedCount)
    {
        static::assertEquals((float) $expectedCount, Utils::getReadingTime($text));
    }

    public function examples()
    {
        $examples = [];
        $finder = (new Finder())->in(__DIR__ . '/samples');
        foreach ($finder->getIterator() as $file) {
            $examples[] = [$file->getContents(), 1];
        }

        return $examples;
    }
}
