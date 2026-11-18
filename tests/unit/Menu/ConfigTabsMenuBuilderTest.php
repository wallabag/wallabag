<?php

namespace Wallabag\Tests\Unit\Menu;

use Knp\Menu\MenuFactory;
use PHPUnit\Framework\TestCase;
use Wallabag\Menu\ConfigTabsMenuBuilder;

class ConfigTabsMenuBuilderTest extends TestCase
{
    public function testAvailableTabs(): void
    {
        $menu = (new ConfigTabsMenuBuilder(new MenuFactory()))->createMenu([]);

        $this->assertSame(['#set1', '#set2', '#set3', '#set4', '#set5', '#set6', '#set7'], array_values(array_map(static fn ($item) => $item->getUri(), $menu->getChildren())));
    }
}
