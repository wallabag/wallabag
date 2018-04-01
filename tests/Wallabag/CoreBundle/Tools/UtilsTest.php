<?php

namespace Tests\Wallabag\CoreBundle\Tools;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Wallabag\CoreBundle\Tools\Utils;

class UtilsTest extends TestCase
{
    /**
     * @dataProvider examples
     */
    public function testCorrectWordsCountForDifferentLanguages($text, $expectedCount)
    {
        static::assertSame((float) $expectedCount, Utils::getReadingTime($text));
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
