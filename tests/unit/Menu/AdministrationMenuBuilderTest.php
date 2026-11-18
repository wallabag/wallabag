<?php

namespace Wallabag\Tests\Unit\Menu;

use Knp\Menu\MenuFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Wallabag\Menu\AdministrationMenuBuilder;

class AdministrationMenuBuilderTest extends TestCase
{
    /** @dataProvider permissions */
    public function testAvailableItems(array $grants, array $expected): void
    {
        $authorization = $this->createMock(AuthorizationCheckerInterface::class);
        $authorization->method('isGranted')->willReturnCallback(static fn (string $permission) => \in_array($permission, $grants, true));
        $builder = new AdministrationMenuBuilder(new MenuFactory(), $authorization);

        $this->assertSame($expected, array_keys($builder->createMenu([])->getChildren()));
    }

    public function permissions(): iterable
    {
        yield 'none' => [[], []];
        yield 'users' => [['LIST_USERS'], ['users']];
        yield 'settings' => [['ROLE_SUPER_ADMIN'], ['settings']];
        yield 'ignore origin rules' => [['LIST_IGNORE_ORIGIN_INSTANCE_RULES'], ['ignore_origin']];
        yield 'all' => [
            ['LIST_USERS', 'ROLE_SUPER_ADMIN', 'LIST_IGNORE_ORIGIN_INSTANCE_RULES'],
            ['users', 'settings', 'ignore_origin'],
        ];
    }
}
