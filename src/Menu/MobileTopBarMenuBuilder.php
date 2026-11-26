<?php

namespace Wallabag\Menu;

use Knp\Menu\Attribute\AsMenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Wallabag\Controller\EntryController;
use Wallabag\Entity\Entry;

class MobileTopBarMenuBuilder
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly MenuProviderInterface $menus,
        private readonly RequestStack $requestStack,
    ) {
    }

    #[AsMenuBuilder(name: 'wallabag_mobile_topbar')]
    public function createMenu(array $options): ItemInterface
    {
        $entry = $options['entry'] ?? null;

        if (!$entry instanceof Entry || !isset($options['redirect']) || !\is_string($options['redirect'])) {
            throw new \InvalidArgumentException('The entry and redirect options are required.');
        }

        $menu = $this->factory->createItem('mobile_topbar');
        $menu->setAttributes([
            'class' => 'hide-on-large-only',
            'data-controller' => 'sticky-nav',
            'data-action' => 'materialize--fab:click@window->sticky-nav#toggle',
        ]);

        $left = $menu->addChild('left');
        $left->addChild('sidebar', [
            'label' => 'menu',
            'linkAttributes' => ['data-target' => 'slide-out', 'class' => 'sidenav-trigger'],
            'extras' => ['kind' => 'button', 'icon' => 'menu'],
        ]);
        $lastList = $this->requestStack->getSession()->get(EntryController::LAST_ENTRIES_LIST_SESSION_KEY);
        $left->addChild('back', [
            'label' => 'menu.left.back_to_homepage',
            'linkAttributes' => ['class' => 'waves-effect'],
            'extras' => ['icon' => 'arrow_back'],
        ] + (null === $lastList ? ['route' => 'homepage'] : ['uri' => $lastList]));

        $right = $menu->addChild('right', ['childrenAttributes' => ['class' => 'right']]);

        foreach ($this->menus->get('wallabag_entry_actions', ['entry' => $entry, 'redirect' => $options['redirect']])->getChildren() as $child) {
            $right->addChild($child->copy());
        }

        $right->removeChild('delete');

        return $menu;
    }
}
