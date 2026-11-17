<?php

namespace Wallabag\Menu;

use Knp\Menu\Attribute\AsMenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

class InternalSettingsTabsMenuBuilder
{
    public function __construct(
        private readonly FactoryInterface $factory,
    ) {
    }

    #[AsMenuBuilder(name: 'wallabag_internal_settings_tabs')]
    public function createMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('tabs');
        $menu->setChildrenAttributes([
            'class' => 'tabs',
            'data-controller' => 'materialize--tabs',
        ]);

        if (!isset($options['sortedSections']) || !\is_array($options['sortedSections'])) {
            throw new \InvalidArgumentException('The sortedSections option must be an array.');
        }

        foreach ($options['sortedSections'] as $section) {
            $menu->addChild('section_' . $section, [
                'uri' => '#set-' . $section,
                'label' => (string) $section,
                'extras' => ['translation_domain' => 'CraueConfigBundle'],
            ]);
        }

        return $menu;
    }
}
