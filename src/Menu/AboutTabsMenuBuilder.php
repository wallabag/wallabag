<?php

namespace Wallabag\Menu;

use Knp\Menu\Attribute\AsMenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

class AboutTabsMenuBuilder
{
    public function __construct(
        private readonly FactoryInterface $factory,
    ) {
    }

    #[AsMenuBuilder(name: 'wallabag_about_tabs')]
    public function createMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('tabs');
        $menu->setChildrenAttributes([
            'class' => 'tabs',
            'data-controller' => 'materialize--tabs',
        ]);
        $menu->setExtra('activate_first', true);
        $menu->addChild('who_behind_wallabag', [
            'uri' => '#set1',
            'label' => 'about.top_menu.who_behind_wallabag',
        ]);
        $menu->addChild('getting_help', [
            'uri' => '#set2',
            'label' => 'about.top_menu.getting_help',
        ]);
        $menu->addChild('helping', [
            'uri' => '#set3',
            'label' => 'about.top_menu.helping',
        ]);
        $menu->addChild('third_party', [
            'uri' => '#set4',
            'label' => 'about.top_menu.third_party',
        ]);

        return $menu;
    }
}
