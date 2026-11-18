<?php

namespace Wallabag\Tests\Unit\Menu;

use Craue\ConfigBundle\Util\Config;
use Knp\Menu\MenuFactory;
use Knp\Menu\Provider\MenuProviderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Wallabag\Menu\AccountMenuBuilder;
use Wallabag\Menu\AdministrationMenuBuilder;
use Wallabag\Menu\ThemeMenuBuilder;

class AccountMenuBuilderTest extends TestCase
{
    /** @dataProvider permissions */
    public function testIndependentPermissionsAndRestrictedAccess(array $grants, bool $restricted, array $expected): void
    {
        $factory = new MenuFactory();
        $authorization = $this->createMock(AuthorizationCheckerInterface::class);
        $authorization->method('isGranted')->willReturnCallback(static fn ($permission) => \in_array($permission, $grants, true));
        $settings = $this->createMock(Config::class);
        $settings->method('get')->with('restricted_access')->willReturn($restricted ? '1' : '0');
        $admin = new AdministrationMenuBuilder($factory, $authorization);
        $theme = new ThemeMenuBuilder($factory);
        $provider = $this->createMock(MenuProviderInterface::class);
        $provider->method('get')->willReturnCallback(static fn ($alias) => ('wallabag_theme' === $alias ? $theme : $admin)->createMenu([]));
        $menu = (new AccountMenuBuilder($factory, $authorization, $settings, $provider))->createMenu([]);

        foreach (['config', 'import', 'site_credentials', 'users', 'settings', 'ignore_origin'] as $name) {
            $this->assertSame(\in_array($name, $expected, true), null !== $menu->getChild($name), $name);
        }

        foreach (['developer', 'light', 'dark', 'auto', 'howto', 'quickstart', 'logout'] as $name) {
            $this->assertNotNull($menu->getChild($name), $name);
        }
    }

    public function permissions(): iterable
    {
        yield 'none' => [[], false, []];
        yield 'config' => [['EDIT_CONFIG'], false, ['config']];
        yield 'import' => [['IMPORT_ENTRIES'], false, ['import']];
        yield 'credentials require setting' => [['LIST_SITE_CREDENTIALS'], false, []];
        yield 'setting requires credentials permission' => [[], true, []];
        yield 'credentials' => [['LIST_SITE_CREDENTIALS'], true, ['site_credentials']];
        yield 'users independently' => [['LIST_USERS'], false, ['users']];
        yield 'settings independently' => [['ROLE_SUPER_ADMIN'], false, ['settings']];
        yield 'ignore origins independently' => [['LIST_IGNORE_ORIGIN_INSTANCE_RULES'], false, ['ignore_origin']];
    }
}
