<?php

namespace Tests\Wallabag\Tools;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Wallabag\Tools\Utils;

class UtilsTest extends TestCase
{
    /**
     * @dataProvider examples
     */
    public function testCorrectWordsCountForDifferentLanguages($filename, $text, $expectedCount)
    {
        static::assertSame($expectedCount, Utils::getReadingTime($text), 'Reading time for: ' . $filename);
    }

    public function examples()
    {
        $examples = [];
        $finder = (new Finder())->in(__DIR__ . '/samples');
        foreach ($finder->getIterator() as $file) {
            preg_match('/-----CONTENT-----\s*(.*?)\s*-----READING_TIME-----\s*(.*)/sx', $file->getContents(), $match);

            if (3 !== \count($match)) {
                throw new \Exception('Sample file "' . $file->getRelativePathname() . '" as wrong definition, see README.');
            }

            $examples[] = [
                $file->getRelativePathname(),
                $match[1], // content
                (int) $match[2], // reading time
            ];
        }

        return $examples;
    }
}
