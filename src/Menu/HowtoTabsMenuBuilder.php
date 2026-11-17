<?php

namespace Wallabag\Menu;

use Knp\Menu\Attribute\AsMenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

class HowtoTabsMenuBuilder
{
    public function __construct(
        private readonly FactoryInterface $factory,
    ) {
    }

    #[AsMenuBuilder(name: 'wallabag_howto_tabs')]
    public function createMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('tabs');
        $menu->setChildrenAttributes([
            'class' => 'tabs',
            'data-controller' => 'materialize--tabs',
        ]);
        $menu->setExtra('activate_first', true);
        $menu->addChild('add_link', [
            'uri' => '#set1',
            'label' => 'howto.tab_menu.add_link',
        ]);
        $menu->addChild('shortcuts', [
            'uri' => '#set2',
            'label' => 'howto.tab_menu.shortcuts',
        ]);

        return $menu;
    }
}
