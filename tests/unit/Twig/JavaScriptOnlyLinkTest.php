<?php

namespace Wallabag\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;

class JavaScriptOnlyLinkTest extends TestCase
{
    private const BOOKMARKLET_TEMPLATE = 'templates/Static/_bookmarklet.html.twig';

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

    /**
     * @dataProvider anchors
     */
    public function testTwigAnchorsDoNotUseEmptyHashDestinations(string $path, string $anchor): void
    {
        self::assertDoesNotMatchRegularExpression(
            '/\\bhref\\s*=\\s*(["\\\'])#\\1/i',
            $anchor,
            \sprintf('Fake fragment link in %s: %s', $path, $anchor)
        );
    }

    /**
     * @dataProvider anchors
     */
    public function testTwigAnchorsDoNotUseJavaScriptDestinations(string $path, string $anchor): void
    {
        if (self::BOOKMARKLET_TEMPLATE === $path) {
            self::assertMatchesRegularExpression(
                '/\\bhref\\s*=\\s*(["\\\'])javascript:/i',
                $anchor,
                \sprintf('Intentional bookmarklet JavaScript link missing in %s: %s', $path, $anchor)
            );

            return;
        }

        self::assertDoesNotMatchRegularExpression(
            '/\\bhref\\s*=\\s*(["\\\'])javascript:/i',
            $anchor,
            \sprintf('JavaScript link in %s: %s', $path, $anchor)
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
            $relativePath = str_replace('\\\\', '/', substr($path, \strlen($projectDirectory) + 1));
            $contents = file_get_contents($path);

            if (false === $contents) {
                self::fail(\sprintf('Unable to read %s.', $relativePath));
            }

            preg_match_all('/<a\\b[^>]*>/is', $contents, $matches);

            foreach ($matches[0] as $index => $anchor) {
                yield \sprintf('%s:%d', $relativePath, $index + 1) => [$relativePath, $anchor];
            }
        }
    }
}
