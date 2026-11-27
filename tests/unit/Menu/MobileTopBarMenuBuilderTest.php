<?php

namespace Wallabag\Tests\Unit\Menu;

use Knp\Menu\MenuFactory;
use Knp\Menu\Provider\MenuProviderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Menu\MobileTopBarMenuBuilder;

class MobileTopBarMenuBuilderTest extends TestCase
{
    /** @dataProvider actions */
    public function testCompositionPreservesAvailableActions(array $names): void
    {
        $factory = new MenuFactory();
        $source = $factory->createItem('actions');

        foreach ($names as $name) {
            $source->addChild($name);
        }

        $provider = $this->createMock(MenuProviderInterface::class);
        $provider->method('get')->with('wallabag_entry_actions', $this->arrayHasKey('entry'))->willReturn($source);
        $menu = (new MobileTopBarMenuBuilder($factory, $provider, $this->createMock(RequestStack::class)))->createMenu(['entry' => new Entry(new User()), 'redirect' => '/view/1']);
        $actions = $menu->getChild('right');

        $this->assertSame(array_values(array_diff($names, ['delete'])), array_keys($actions->getChildren()));
        $this->assertSame($names, array_keys($source->getChildren()));

        foreach ($actions->getChildren() as $name => $item) {
            $this->assertNotSame($source->getChild($name), $item);
        }
    }

    public function actions(): iterable
    {
        yield 'none' => [[]];
        yield 'native actions' => [['archive', 'star', 'delete']];
        yield 'delete only' => [['delete']];
        yield 'decorated actions' => [['custom', 'delete', 'star']];
    }

    public function testMissingRedirectOption(): void
    {
        $builder = new MobileTopBarMenuBuilder(new MenuFactory(), $this->createMock(MenuProviderInterface::class), $this->createMock(RequestStack::class));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The entry and redirect options are required.');

        $builder->createMenu(['entry' => new Entry(new User())]);
    }
}
