<?php

namespace Wallabag\Tests\Unit\Menu;

use Craue\ConfigBundle\Util\Config;
use Knp\Menu\MenuFactory;
use Knp\Menu\Provider\MenuProviderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Menu\EntryMenuBuilder;
use Wallabag\Twig\WallabagExtension;

class EntryMenuBuilderTest extends TestCase
{
    /** @dataProvider availability */
    public function testReaderSpecificActions(array $grants, bool $print, array $expected): void
    {
        $entry = new Entry(new User());
        $entry->setUrl('https://example.com/article');
        $factory = new MenuFactory();
        $authorization = $this->createMock(AuthorizationCheckerInterface::class);
        $authorization->method('isGranted')->willReturnCallback(static fn ($permission) => \in_array($permission, $grants, true));
        $settings = $this->createMock(Config::class);
        $settings->method('get')->with('show_printlink')->willReturn($print ? '1' : '0');
        $provider = $this->createMock(MenuProviderInterface::class);
        $provider->method('get')->willReturnCallback(static fn () => $factory->createItem('child'));
        $extension = $this->createMock(WallabagExtension::class);
        $extension->method('generateArticleReportingUrl')->willReturn('mailto:report@example.com');
        $menu = (new EntryMenuBuilder($factory, $authorization, $settings, $provider, $extension))->createMenu(['entry' => $entry, 'redirect' => '/view/1']);

        foreach (['reload', 'random', 'print'] as $name) {
            $this->assertSame(\in_array($name, $expected, true), null !== $menu->getChild($name), $name);
        }

        $this->assertNotNull($menu->getChild('add_tag'));
    }

    public function availability(): iterable
    {
        yield 'none' => [[], false, []];
        yield 'reload' => [['RELOAD'], false, ['reload']];
        yield 'random' => [['LIST_ENTRIES'], false, ['random']];
        yield 'print setting' => [[], true, ['print']];
    }

    public function testMissingRedirectOption(): void
    {
        $builder = new EntryMenuBuilder(
            new MenuFactory(),
            $this->createMock(AuthorizationCheckerInterface::class),
            $this->createMock(Config::class),
            $this->createMock(MenuProviderInterface::class),
            $this->createMock(WallabagExtension::class),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The entry and redirect options are required.');

        $builder->createMenu(['entry' => new Entry(new User())]);
    }
}
