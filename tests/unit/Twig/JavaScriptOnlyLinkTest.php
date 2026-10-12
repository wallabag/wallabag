<?php

namespace Wallabag\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;

class JavaScriptOnlyLinkTest extends TestCase
{
    private const BOOKMARKLET_TEMPLATE = 'templates/Static/_bookmarklet.html.twig';

    private const ACCESSIBLE_ICON_BUTTON_MARKERS = [
        'templates/Config/index.html.twig' => ['config-help'],
        'templates/Entry/entries.html.twig' => ['data-target="filters"', 'data-target="export"'],
        'templates/Entry/entry.html.twig' => ['class="sidenav-trigger"', 'data-toggle="actions"'],
        'templates/Entry/new_form.html.twig' => ['class="nav-form-button"'],
        'templates/Entry/search_form.html.twig' => ['class="nav-form-button"'],
        'templates/layout.html.twig' => ['class="sidenav-trigger"', 'data-shortcuts-target="showSearch"', 'id="news_menu"'],
    ];

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

    /**
     * @dataProvider iconButtons
     */
    public function testIconOnlyTwigButtonsHaveAccessibleNames(string $path, string $button): void
    {
        self::assertMatchesRegularExpression(
            '/\\baria-label\\s*=/i',
            $button,
            \sprintf('Icon-only button without an accessible name in %s: %s', $path, $button)
        );
        self::assertMatchesRegularExpression(
            '/<i\\b[^>]*\\bclass\\s*=\\s*(["\\\'])[^"\\\']*\\bmaterial-icons\\b[^"\\\']*\\1[^>]*\\baria-hidden\\s*=\\s*(["\\\'])true\\2/i',
            $button,
            \sprintf('Decorative icon exposed to assistive technology in %s: %s', $path, $button)
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

    public function iconButtons(): iterable
    {
        $projectDirectory = \dirname(__DIR__, 3);
        foreach (self::ACCESSIBLE_ICON_BUTTON_MARKERS as $relativePath => $markers) {
            $path = $projectDirectory . '/' . $relativePath;
            $contents = file_get_contents($path);

            if (false === $contents) {
                self::fail(\sprintf('Unable to read %s.', $relativePath));
            }

            foreach ($markers as $marker) {
                preg_match_all(
                    '/<button\\b(?=[^>]*' . preg_quote($marker, '/') . ')[^>]*>.*?<\\/button>/is',
                    $contents,
                    $matches
                );

                foreach ($matches[0] as $index => $button) {
                    yield \sprintf('%s:%s:%d', $relativePath, $marker, $index + 1) => [$relativePath, $button];
                }
            }
        }
    }
}
