<?php

namespace Wallabag\Menu;

use Knp\Menu\Attribute\AsMenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AdministrationMenuBuilder
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly AuthorizationCheckerInterface $authorization,
    ) {
    }

    #[AsMenuBuilder(name: 'wallabag_administration')]
    public function createMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('administration');

        if ($this->authorization->isGranted('LIST_USERS')) {
            $menu->addChild('users', [
                'route' => 'user_index',
                'label' => 'menu.left.users_management',
                'extras' => ['icon' => 'people'],
            ]);
        }

        if ($this->authorization->isGranted('ROLE_SUPER_ADMIN')) {
            $menu->addChild('settings', [
                'route' => 'craue_config_settings_modify',
                'label' => 'menu.left.internal_settings',
                'extras' => ['icon' => 'settings'],
            ]);
        }

        if ($this->authorization->isGranted('LIST_IGNORE_ORIGIN_INSTANCE_RULES')) {
            $menu->addChild('ignore_origin', [
                'route' => 'ignore_origin_instance_rules_index',
                'label' => 'menu.left.ignore_origin_instance_rules',
                'extras' => ['icon' => 'build'],
            ]);
        }

        return $menu;
    }
}
