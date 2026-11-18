<?php

namespace Wallabag\Menu;

use Craue\ConfigBundle\Util\Config;
use Knp\Menu\Attribute\AsMenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Wallabag\Entity\Entry;
use Wallabag\Twig\WallabagExtension;

class EntryMenuBuilder
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly Config $settings,
        private readonly MenuProviderInterface $menus,
        private readonly WallabagExtension $extension,
    ) {
    }

    #[AsMenuBuilder(name: 'wallabag_entry')]
    public function createMenu(array $options): ItemInterface
    {
        $entry = $options['entry'] ?? null;

        if (!$entry instanceof Entry || !isset($options['redirect']) || !\is_string($options['redirect'])) {
            throw new \InvalidArgumentException('The entry and redirect options are required.');
        }

        $menu = $this->factory->createItem('entry');
        $menu->setChildrenAttributes([
            'id' => 'slide-out',
            'class' => 'left-bar collapsible sidenav sidenav-fixed reader-mode',
            'data-controller' => 'materialize--sidenav materialize--collapsible leftbar',
        ]);

        $menu->addChild('back', [
            'route' => 'homepage',
            'label' => 'entry.view.left_menu.back_to_homepage',
            'attributes' => ['class' => 'border-bottom hide-on-med-and-down'],
            'extras' => ['icon' => 'arrow_back'],
        ]);
        $menu->addChild('original', [
            'uri' => $entry->getUrl(),
            'label' => 'entry.view.left_menu.view_original_article',
            'attributes' => ['class' => 'border-bottom hide-on-med-and-down'],
            'linkAttributes' => [
                'target' => '_blank',
                'rel' => 'noopener',
                'data-shortcuts-target' => 'openOriginal',
                'class' => 'original',
            ],
            'extras' => ['icon' => 'link'],
        ]);

        if ($this->authorization->isGranted('RELOAD', $entry)) {
            $menu->addChild('reload', [
                'route' => 'reload_entry',
                'routeParameters' => ['id' => $entry->getId()],
                'label' => 'entry.view.left_menu.re_fetch_content',
                'linkAttributes' => ['title' => 'entry.view.left_menu.re_fetch_content'],
                'extras' => [
                    'kind' => 'form',
                    'icon' => 'refresh',
                    'csrf_token_id' => 'reload-entry',
                    'confirmation' => 'entry.confirm.reload',
                ],
            ]);
        }

        foreach ($this->menus->get('wallabag_entry_actions', ['entry' => $entry, 'redirect' => $options['redirect']])->getChildren() as $child) {
            $menu->addChild($child->copy());
        }

        if (null !== $archive = $menu->getChild('archive')) {
            $archive->setLinkAttribute('data-shortcuts-target', 'markAsRead');
            $archive->setAttribute('class', trim($archive->getAttribute('class', '') . ' hide-on-med-and-down'));
        }

        if (null !== $star = $menu->getChild('star')) {
            $star->setLinkAttribute('data-shortcuts-target', 'markAsFavorite');
            $star->setLinkAttribute('class', trim($star->getLinkAttribute('class', '') . ' favorite'));
            $star->setAttribute('class', trim($star->getAttribute('class', '') . ' hide-on-med-and-down'));
        }

        if (null !== $delete = $menu->getChild('delete')) {
            $delete->setLinkAttribute('data-shortcuts-target', 'deleteEntry');
            $delete->setLinkAttribute('class', trim($delete->getLinkAttribute('class', '') . ' delete'));
            $delete->setAttribute('class', trim($delete->getAttribute('class', '') . ' border-bottom'));
        }

        $menu->addChild('add_tag', [
            'label' => 'entry.view.left_menu.add_a_tag',
            'attributes' => ['class' => 'border-bottom'],
            'linkAttributes' => [
                'id' => 'nav-btn-add-tag',
                'data-action' => 'click->materialize--sidenav#close click->leftbar#toggleAddTagForm',
            ],
            'extras' => ['kind' => 'button', 'icon' => 'label_outline'],
        ]);

        $theme = $menu->addChild('theme', [
            'label' => 'entry.view.left_menu.theme_toggle',
            'extras' => ['kind' => 'group', 'icon' => 'brightness_medium'],
        ]);

        foreach ($this->menus->get('wallabag_theme')->getChildren() as $child) {
            $theme->addChild($child->copy());
        }

        $share = $menu->addChild('share', [
            'label' => 'entry.view.left_menu.share_content',
            'extras' => ['kind' => 'group', 'icon' => 'share'],
        ]);

        foreach ($this->menus->get('wallabag_entry_share', ['entry' => $entry])->getChildren() as $child) {
            $share->addChild($child->copy());
        }

        if ($this->settings->get('show_printlink')) {
            $menu->addChild('print', [
                'label' => 'entry.view.left_menu.print',
                'attributes' => ['class' => 'border-bottom hide-on-med-and-down'],
                'linkAttributes' => [
                    'data-controller' => 'print',
                    'data-action' => 'click->print#print:prevent',
                    'title' => 'entry.view.left_menu.print',
                ],
                'extras' => ['kind' => 'button', 'icon' => 'print'],
            ]);
        }

        if ($this->authorization->isGranted('LIST_ENTRIES')) {
            $menu->addChild('random', [
                'route' => 'random_entry',
                'routeParameters' => ['type' => 'all'],
                'label' => 'menu.top.random_entry',
                'linkAttributes' => ['title' => 'menu.top.random_entry'],
                'attributes' => ['class' => 'border-top border-bottom'],
                'extras' => ['icon' => 'casino'],
            ]);
        }

        $exports = $menu->addChild('export', [
            'label' => 'entry.view.left_menu.export',
            'extras' => ['kind' => 'group', 'icon' => 'file_download'],
        ]);

        foreach ($this->menus->get('wallabag_entry_exports', ['entry' => $entry])->getChildren() as $child) {
            $exports->addChild($child->copy());
        }

        $menu->addChild('report', [
            'uri' => $this->extension->generateArticleReportingUrl($entry),
            'label' => 'entry.view.left_menu.problem.label',
            'linkAttributes' => ['title' => 'entry.view.left_menu.problem.description'],
            'extras' => ['icon' => 'error'],
        ]);

        return $menu;
    }
}
