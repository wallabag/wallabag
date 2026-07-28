<?php

namespace Wallabag\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;

class JavaScriptOnlyLinkTest extends TestCase
{
    /**
     * @dataProvider anchors
     */
    public function testTwigAnchorsHaveHref(string $path, string $anchor): void
    {
        self::assertMatchesRegularExpression(
            '/\\bhref\\s*=/i',
            $anchor,
            \sprintf('Anchor without href in %s: %s', $path, $anchor)
        );
    }

    public function anchors(): iterable
    {
        $projectDirectory = \dirname(__DIR__, 3);
        $templates = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($projectDirectory . '/templates', \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($templates as $template) {
            if (!$template->isFile() || 'twig' !== $template->getExtension()) {
                continue;
            }

            $path = $template->getPathname();
            $contents = file_get_contents($path);

            if (false === $contents) {
                self::fail(\sprintf('Unable to read %s.', $path));
            }

            preg_match_all('/<a\\b[^>]*>/is', $contents, $matches);

            foreach ($matches[0] as $index => $anchor) {
                yield \sprintf('%s:%d', $path, $index + 1) => [$path, $anchor];
            }
        }
    }
}
