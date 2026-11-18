<?php

namespace Wallabag\Menu;

use Knp\Menu\Attribute\AsMenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Wallabag\Entity\Entry;

class FabMenuBuilder
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly MenuProviderInterface $menus,
    ) {
    }

    #[AsMenuBuilder(name: 'wallabag_fab')]
    public function createMenu(array $options): ItemInterface
    {
        $entry = $options['entry'] ?? null;

        if (!$entry instanceof Entry || !isset($options['redirect']) || !\is_string($options['redirect'])) {
            throw new \InvalidArgumentException('The entry and redirect options are required.');
        }

        $menu = $this->factory->createItem('fab');
        $menu->setAttributes([
            'class' => 'fixed-action-btn hide-on-large-only',
            'data-controller' => 'materialize--fab',
            'data-action' => 'scroll@window->materialize--fab#autoDisplay click->materialize--fab#click',
        ]);

        foreach ($this->menus->get('wallabag_entry_actions', ['entry' => $entry, 'redirect' => $options['redirect']])->getChildren() as $child) {
            $menu->addChild($child->copy());
        }

        return $menu;
    }
}
