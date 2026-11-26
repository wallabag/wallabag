<?php

namespace Wallabag\Menu;

use Knp\Menu\Attribute\AsMenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

class ThemeMenuBuilder
{
    public function __construct(
        private readonly FactoryInterface $factory,
    ) {
    }

    #[AsMenuBuilder(name: 'wallabag_theme')]
    public function createMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('theme');
        $menu->addChild('light', [
            'label' => 'theme.action.toggle_light',
            'linkAttributes' => ['data-action' => 'click->dark-theme#useLight', 'class' => 'theme-toggle-icon', 'title' => 'theme.action.toggle_light'],
            'extras' => ['kind' => 'button', 'icon' => 'brightness_high'],
        ]);
        $menu->addChild('dark', [
            'label' => 'theme.action.toggle_dark',
            'linkAttributes' => ['data-action' => 'click->dark-theme#useDark', 'class' => 'theme-toggle-icon', 'title' => 'theme.action.toggle_dark'],
            'extras' => ['kind' => 'button', 'icon' => 'brightness_low'],
        ]);
        $menu->addChild('auto', [
            'label' => 'theme.action.toggle_auto',
            'linkAttributes' => ['data-action' => 'click->dark-theme#useAuto', 'class' => 'theme-toggle-icon', 'title' => 'theme.action.toggle_auto'],
            'extras' => ['kind' => 'button', 'icon' => 'brightness_auto'],
        ]);

        return $menu;
    }
}
