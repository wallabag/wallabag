<?php

namespace Wallabag\Tests\Unit\Twig;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\TwigFunction;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Repository\EntryRepository;
use Wallabag\Twig\WallabagExtension;

class WallabagExtensionTest extends TestCase
{
    public static function dataForRemoveDataAttributes(): iterable
    {
        yield 'controller attributes' => ['<div data-controller="annotations" data-annotations-search-url-value="http://example.org/c"><p>text</p></div>', '<div><p>text</p></div>'];
        yield 'greater than sign in a value' => ['<div title="a>b" data-controller="annotations">text</div>', '<div title="a>b">text</div>'];
        yield 'no space after a quoted value' => ['<div class="a"data-controller="annotations">text</div>', '<div class="a">text</div>'];
        yield 'slash as separator' => ['<div/data-controller="annotations">text</div>', '<div>text</div>'];
        yield 'uppercase and single quotes' => ["<div DATA-Controller='annotations'>text</div>", '<div>text</div>'];
        yield 'no content' => [null, ''];
        yield 'text is kept' => ['<p>data-foo="bar" and a &lt; b</p>', '<p>data-foo="bar" and a &lt; b</p>'];
    }

    /**
     * @dataProvider dataForRemoveDataAttributes
     */
    public function testRemoveDataAttributes(?string $html, string $expected): void
    {
        $this->assertSame($expected, $this->createExtension()->removeDataAttributes($html));
    }

    public function testRemoveWww(): void
    {
        $extension = $this->createExtension();

        $this->assertSame('lemonde.fr', $extension->removeWww('www.lemonde.fr'));
        $this->assertSame('lemonde.fr', $extension->removeWww('lemonde.fr'));
        $this->assertSame('gist.github.com', $extension->removeWww('gist.github.com'));
    }

    public function testRemoveScheme(): void
    {
        $extension = $this->createExtension();

        $this->assertSame('lemonde.fr', $extension->removeScheme('lemonde.fr'));
        $this->assertSame('gist.github.com', $extension->removeScheme('gist.github.com'));
        $this->assertSame('gist.github.com', $extension->removeScheme('https://gist.github.com'));
        $this->assertSame('gist.github.com', $extension->removeScheme('http://gist.github.com'));
    }

    public function testRemoveSchemeAndWww(): void
    {
        $extension = $this->createExtension();

        $this->assertSame('lemonde.fr', $extension->removeSchemeAndWww('www.lemonde.fr'));
        $this->assertSame('lemonde.fr', $extension->removeSchemeAndWww('http://www.lemonde.fr'));
        $this->assertSame('lemonde.fr', $extension->removeSchemeAndWww('http://lemonde.fr'));
        $this->assertSame('lemonde.fr', $extension->removeSchemeAndWww('https://www.lemonde.fr'));
        $this->assertSame('lemonde.fr', $extension->removeSchemeAndWww('https://lemonde.fr'));
        $this->assertSame('gist.github.com', $extension->removeSchemeAndWww('https://gist.github.com'));
        $this->assertSame('ftp://gist.github.com', $extension->removeSchemeAndWww('ftp://gist.github.com'));
    }

    public function testGenerateArticleReportingUrlFunctionIsRegistered(): void
    {
        $functionNames = array_map(
            static fn (TwigFunction $function): string => $function->getName(),
            $this->createExtension()->getFunctions(),
        );

        $this->assertContains('generate_article_reporting_url', $functionNames);
    }

    /**
     * @dataProvider articleReportingUrlProvider
     */
    public function testGenerateArticleReportingUrl(string $reportingUrl, string $expectedUrl): void
    {
        $entry = new Entry(new User());
        $entry->setUrl('https://example.com/article?id=1&lang=en');

        $this->assertSame(
            $expectedUrl,
            $this->createExtension($reportingUrl)->generateArticleReportingUrl($entry),
        );
    }

    public function articleReportingUrlProvider(): iterable
    {
        yield 'default mailto URL' => [
            'mailto:siteconfig@wallabag.org?subject=Wrong%20display%20in%20wallabag',
            'mailto:siteconfig@wallabag.org?subject=Wrong%20display%20in%20wallabag&body=https%3A%2F%2Fexample.com%2Farticle%3Fid%3D1%26lang%3Den',
        ];
        yield 'custom mailto subject' => [
            'mailto:support@example.com?subject=Custom%20subject&priority=high',
            'mailto:support@example.com?subject=Custom%20subject&priority=high&body=https%3A%2F%2Fexample.com%2Farticle%3Fid%3D1%26lang%3Den',
        ];
        yield 'HTTPS URL replaces body and preserves fragment' => [
            'https://support.example.com/issues/new?template=article&subject=Custom%20subject&body=old#report',
            'https://support.example.com/issues/new?template=article&subject=Custom%20subject&body=https%3A%2F%2Fexample.com%2Farticle%3Fid%3D1%26lang%3Den#report',
        ];
    }

    private function createExtension(string $articleReportingUrl = 'mailto:support@example.com'): WallabagExtension
    {
        $entryRepository = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tokenStorage = $this->getMockBuilder(TokenStorageInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $translator = $this->getMockBuilder(TranslatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        return new WallabagExtension($entryRepository, $tokenStorage, 0, $translator, '', $articleReportingUrl);
    }
}
