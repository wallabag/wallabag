<?php

namespace Wallabag\Tests\Unit\Menu;

use Knp\Menu\MenuFactory;
use PHPUnit\Framework\TestCase;
use Wallabag\Menu\InternalSettingsTabsMenuBuilder;

class InternalSettingsTabsMenuBuilderTest extends TestCase
{
    public function testAvailableTabs(): void
    {
        $menu = (new InternalSettingsTabsMenuBuilder(new MenuFactory()))->createMenu(['sortedSections' => ['first', null, 'last']]);

        $this->assertSame(['#set-first', '#set-', '#set-last'], array_values(array_map(static fn ($item) => $item->getUri(), $menu->getChildren())));
    }

    public function testMissingSortedSectionsOption(): void
    {
        $builder = new InternalSettingsTabsMenuBuilder(new MenuFactory());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The sortedSections option must be an array.');

        $builder->createMenu([]);
    }
}
