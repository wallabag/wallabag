<?php

namespace Wallabag\Menu;

use Craue\ConfigBundle\Util\Config;
use Knp\Menu\Attribute\AsMenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Wallabag\Entity\Entry;

class EntryShareMenuBuilder
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly Config $settings,
    ) {
    }

    #[AsMenuBuilder(name: 'wallabag_entry_share')]
    public function createMenu(array $options): ItemInterface
    {
        $entry = $options['entry'] ?? null;

        if (!$entry instanceof Entry) {
            throw new \InvalidArgumentException('The entry option must be an Entry.');
        }

        $menu = $this->factory->createItem('entry_share');

        if ($this->settings->get('share_public')) {
            if ($this->authorization->isGranted('SHARE', $entry)) {
                $menu->addChild('public_link', [
                    'route' => 'share',
                    'routeParameters' => ['id' => $entry->getId()],
                    'label' => 'entry.view.left_menu.public_link',
                    'linkAttributes' => ['class' => 'btn-link tool icon-eye', 'formtarget' => '_blank', 'title' => 'entry.view.left_menu.public_link'],
                    'extras' => ['kind' => 'form', 'csrf_token_id' => 'share-entry'],
                ]);
            }

            if ($this->authorization->isGranted('UNSHARE', $entry) && $entry->getUid()) {
                $menu->addChild('delete_public_link', [
                    'route' => 'delete_share',
                    'routeParameters' => ['id' => $entry->getId()],
                    'label' => 'entry.view.left_menu.delete_public_link',
                    'linkAttributes' => ['class' => 'btn-link tool icon-no-eye', 'title' => 'entry.view.left_menu.delete_public_link'],
                    'extras' => ['kind' => 'form', 'csrf_token_id' => 'delete-share'],
                ]);
            }
        }

        $title = strip_tags((string) $entry->getTitle());
        $url = rawurlencode($entry->getUrl());

        if ($this->settings->get('share_twitter')) {
            $menu->addChild('twitter', [
                'uri' => 'https://twitter.com/share?text=' . rawurlencode($title . ' ' . $entry->getUrl() . ' via @wallabagapp'),
                'label' => 'twitter',
                'linkAttributes' => [
                    'class' => 'tool icon-twitter',
                    'title' => 'twitter',
                    'target' => '_blank',
                    'rel' => 'noopener',
                ],
            ]);
        }

        if ($this->settings->get('share_shaarli')) {
            $tags = rawurlencode(strip_tags(implode(',', $entry->getTags()->toArray())));
            $originalUrl = $this->settings->get('shaarli_share_origin_url')
                ? '&original_url=' . rawurlencode((string) $entry->getOriginUrl())
                : '';

            $menu->addChild('shaarli', [
                'uri' => $this->settings->get('shaarli_url') . '/index.php?post=' . $url
                . '&title=' . rawurlencode($title) . '&tags=' . $tags . $originalUrl,
                'label' => 'shaarli',
                'linkAttributes' => [
                    'class' => 'tool icon-image shaarli',
                    'title' => 'shaarli',
                    'target' => '_blank',
                    'rel' => 'noopener',
                ],
            ]);
        }

        if ($this->settings->get('share_diaspora')) {
            $menu->addChild('diaspora', [
                'uri' => $this->settings->get('diaspora_url') . '/bookmarklet?url=' . $url
                . '&title=' . rawurlencode($title) . '&notes=&v=1&noui=1&jump=doclose',
                'label' => 'diaspora*',
                'linkAttributes' => [
                    'class' => 'tool icon-image diaspora',
                    'title' => 'diaspora',
                    'target' => '_blank',
                    'rel' => 'noopener',
                ],
            ]);
        }

        if ($this->settings->get('share_unmark')) {
            $menu->addChild('unmark', [
                'uri' => $this->settings->get('unmark_url') . '/mark/add?url=' . $url
                . '&title=' . rawurlencode($title) . '&v=6',
                'label' => 'unmark.it',
                'linkAttributes' => [
                    'class' => 'tool icon-image unmark',
                    'title' => 'unmark',
                    'target' => '_blank',
                    'rel' => 'noopener',
                ],
            ]);
        }

        if ($this->settings->get('share_linkding')) {
            $menu->addChild('linkding', [
                'uri' => $this->settings->get('linkding_url') . '/bookmarks/new?url=' . $url,
                'label' => 'linkding',
                'linkAttributes' => [
                    'class' => 'tool icon-image linkding',
                    'title' => 'linkding',
                    'target' => '_blank',
                    'rel' => 'noopener',
                ],
            ]);
        }

        if ($this->settings->get('share_mail')) {
            $menu->addChild('mail', [
                'uri' => 'mailto:?subject=' . rawurlencode($title) . '&body=' . $url . '%20via%20@wallabagapp',
                'label' => 'entry.view.left_menu.share_email_label',
                'linkAttributes' => [
                    'class' => 'tool icon',
                    'title' => 'entry.view.left_menu.share_email_label',
                ],
                'extras' => ['icon' => 'mail'],
            ]);
        }

        return $menu;
    }
}
