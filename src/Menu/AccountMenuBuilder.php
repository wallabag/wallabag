<?php

namespace Wallabag\Menu;

use Craue\ConfigBundle\Util\Config;
use Knp\Menu\Attribute\AsMenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AccountMenuBuilder
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly Config $settings,
        private readonly MenuProviderInterface $menus,
    ) {
    }

    #[AsMenuBuilder(name: 'wallabag_account')]
    public function createMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('account');

        if ($this->authorization->isGranted('EDIT_CONFIG')) {
            $menu->addChild('config', ['route' => 'config', 'label' => 'menu.left.config', 'extras' => ['icon' => 'settings']]);
        }

        $menu->addChild('developer', ['route' => 'developer', 'label' => 'menu.left.developer', 'extras' => ['icon' => 'smartphone']]);

        if ($this->authorization->isGranted('IMPORT_ENTRIES')) {
            $menu->addChild('import', ['route' => 'import', 'label' => 'menu.left.import', 'extras' => ['icon' => 'import_export']]);
        }

        if ($this->settings->get('restricted_access') && $this->authorization->isGranted('LIST_SITE_CREDENTIALS')) {
            $menu->addChild('site_credentials', ['route' => 'site_credentials_index', 'label' => 'menu.left.site_credentials', 'extras' => ['icon' => 'vpn_key']]);
        }

        $menu->addChild('before_administration', ['extras' => ['kind' => 'separator']]);

        foreach ($this->menus->get('wallabag_administration')->getChildren() as $child) {
            $menu->addChild($child->copy());
        }

        $menu->addChild('before_theme', ['extras' => ['kind' => 'separator']]);

        foreach ($this->menus->get('wallabag_theme')->getChildren() as $child) {
            $menu->addChild($child->copy());
        }

        $menu->addChild('after_theme', ['extras' => ['kind' => 'separator']]);
        $menu->addChild('howto', ['route' => 'howto', 'label' => 'menu.left.howto', 'extras' => ['icon' => 'live_help']]);
        $menu->addChild('quickstart', ['route' => 'quickstart', 'label' => 'menu.left.quickstart', 'extras' => ['icon' => 'assistant_photo']]);
        $menu->addChild('before_logout', ['extras' => ['kind' => 'separator']]);
        $menu->addChild('logout', ['route' => 'fos_user_security_logout', 'label' => 'menu.left.logout', 'extras' => ['icon' => 'input']]);

        return $menu;
    }
}
