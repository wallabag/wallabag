<?php

namespace Wallabag\Tests\Unit\Menu;

use Knp\Menu\MenuFactory;
use PHPUnit\Framework\TestCase;
use Wallabag\Menu\ThemeMenuBuilder;

class ThemeMenuBuilderTest extends TestCase
{
    public function testThemeActionsAndTheirOrder(): void
    {
        $menu = (new ThemeMenuBuilder(new MenuFactory()))->createMenu([]);

        $this->assertSame(['light', 'dark', 'auto'], array_keys($menu->getChildren()));
        $this->assertSame('theme.action.toggle_light', $menu->getChild('light')->getLabel());
        $this->assertSame('theme.action.toggle_dark', $menu->getChild('dark')->getLabel());
        $this->assertSame('theme.action.toggle_auto', $menu->getChild('auto')->getLabel());
        $this->assertSame('click->dark-theme#useLight', $menu->getChild('light')->getLinkAttribute('data-action'));
        $this->assertSame('click->dark-theme#useDark', $menu->getChild('dark')->getLinkAttribute('data-action'));
        $this->assertSame('click->dark-theme#useAuto', $menu->getChild('auto')->getLinkAttribute('data-action'));
    }
}
