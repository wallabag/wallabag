<?php

namespace Wallabag\Menu;

use Knp\Menu\Attribute\AsMenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Wallabag\Entity\Entry;

class EntryActionsMenuBuilder
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly AuthorizationCheckerInterface $authorization,
    ) {
    }

    #[AsMenuBuilder(name: 'wallabag_entry_actions')]
    public function createMenu(array $options): ItemInterface
    {
        $entry = $options['entry'] ?? null;

        if (!$entry instanceof Entry || !isset($options['redirect']) || !\is_string($options['redirect'])) {
            throw new \InvalidArgumentException('The entry and redirect options are required.');
        }

        $menu = $this->factory->createItem('entry_actions');

        if ($this->authorization->isGranted('ARCHIVE', $entry)) {
            $label = $entry->isArchived() ? 'entry.action.set_as_unread' : 'entry.action.set_as_read';
            $menu->addChild('archive', [
                'route' => 'archive_entry',
                'routeParameters' => ['id' => $entry->getId(), 'redirect' => $options['redirect']],
                'label' => $label,
                'linkAttributes' => ['title' => $label],
                'extras' => [
                    'kind' => 'form',
                    'icon' => $entry->isArchived() ? 'outbox' : 'archive',
                    'csrf_token_id' => 'archive-entry',
                ],
            ]);
        }

        if ($this->authorization->isGranted('STAR', $entry)) {
            $label = $entry->isStarred() ? 'entry.action.unstar' : 'entry.action.star';
            $menu->addChild('star', [
                'route' => 'star_entry',
                'routeParameters' => ['id' => $entry->getId(), 'redirect' => $options['redirect']],
                'label' => $label,
                'linkAttributes' => ['title' => $label],
                'extras' => [
                    'kind' => 'form',
                    'icon' => $entry->isStarred() ? 'star' : 'star_outline',
                    'csrf_token_id' => 'star-entry',
                ],
            ]);
        }

        if ($this->authorization->isGranted('DELETE', $entry)) {
            $menu->addChild('delete', [
                'route' => 'delete_entry',
                'routeParameters' => ['id' => $entry->getId(), 'redirect' => $options['redirect']],
                'label' => 'entry.action.delete',
                'linkAttributes' => ['title' => 'entry.action.delete'],
                'extras' => [
                    'kind' => 'form',
                    'icon' => 'delete',
                    'csrf_token_id' => 'delete-entry',
                    'confirmation' => 'entry.confirm.delete',
                ],
            ]);
        }

        return $menu;
    }
}
