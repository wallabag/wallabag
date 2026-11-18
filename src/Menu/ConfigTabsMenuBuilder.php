<?php

namespace Wallabag\Menu;

use Knp\Menu\Attribute\AsMenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

class ConfigTabsMenuBuilder
{
    public function __construct(
        private readonly FactoryInterface $factory,
    ) {
    }

    #[AsMenuBuilder(name: 'wallabag_config_tabs')]
    public function createMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('tabs');
        $menu->setChildrenAttributes([
            'class' => 'tabs',
            'data-controller' => 'materialize--tabs',
        ]);
        $menu->setExtra('activate_first', true);
        $menu->addChild('settings', [
            'uri' => '#set1',
            'label' => 'config.tab_menu.settings',
        ]);
        $menu->addChild('feed', [
            'uri' => '#set2',
            'label' => 'config.tab_menu.feed',
        ]);
        $menu->addChild('user_info', [
            'uri' => '#set3',
            'label' => 'config.tab_menu.user_info',
        ]);
        $menu->addChild('password', [
            'uri' => '#set4',
            'label' => 'config.tab_menu.password',
        ]);
        $menu->addChild('rules', [
            'uri' => '#set5',
            'label' => 'config.tab_menu.rules',
        ]);
        $menu->addChild('ignore_origin', [
            'uri' => '#set6',
            'label' => 'config.tab_menu.ignore_origin',
        ]);
        $menu->addChild('reset', [
            'uri' => '#set7',
            'label' => 'config.tab_menu.reset',
        ]);

        return $menu;
    }
}
