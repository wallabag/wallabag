<?php

namespace Wallabag\Menu;

use Doctrine\ORM\QueryBuilder;
use Knp\Menu\Attribute\AsMenuBuilder;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Wallabag\Entity\User;
use Wallabag\Repository\AnnotationRepository;
use Wallabag\Repository\EntryRepository;
use Wallabag\Repository\FeedItemRepository;
use Wallabag\Repository\TagRepository;

class MainMenuBuilder
{
    public function __construct(
        private readonly FactoryInterface $factory,
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly EntryRepository $entryRepository,
        private readonly AnnotationRepository $annotationRepository,
        private readonly TagRepository $tagRepository,
        private readonly int $lifeTime,
        private readonly FeedItemRepository $feedItemRepository,
    ) {
    }

    #[AsMenuBuilder(name: 'wallabag_main')]
    public function createMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('main');

        if ($this->authorization->isGranted('LIST_ENTRIES')) {
            $menu->addChild('unread', [
                'route' => 'unread',
                'label' => 'menu.left.unread',
                'attributes' => ['class' => 'bold'],
                'linkAttributes' => ['class' => 'waves-effect'],
                'extras' => [
                    'counter' => $this->countEntries('unread'),
                    'routes' => [
                        ['route' => 'search', 'query_parameters' => ['currentRoute' => 'unread']],
                        ['route' => 'search', 'query_parameters' => ['currentRoute' => 'homepage']],
                    ],
                ],
            ]);
            $menu->addChild('starred', [
                'route' => 'starred',
                'label' => 'menu.left.starred',
                'attributes' => ['class' => 'bold'],
                'linkAttributes' => ['class' => 'waves-effect'],
                'extras' => [
                    'counter' => $this->countEntries('starred'),
                    'routes' => [
                        ['route' => 'search', 'query_parameters' => ['currentRoute' => 'starred']],
                    ],
                ],
            ]);
            $menu->addChild('archive', [
                'route' => 'archive',
                'label' => 'menu.left.archive',
                'attributes' => ['class' => 'bold'],
                'linkAttributes' => ['class' => 'waves-effect'],
                'extras' => [
                    'counter' => $this->countEntries('archive'),
                    'routes' => [
                        ['route' => 'search', 'query_parameters' => ['currentRoute' => 'archive']],
                    ],
                ],
            ]);
            $menu->addChild('annotated', [
                'route' => 'annotated',
                'label' => 'menu.left.with_annotations',
                'attributes' => ['class' => 'bold'],
                'linkAttributes' => ['class' => 'waves-effect'],
                'extras' => [
                    'counter' => $this->countEntries('annotated'),
                    'routes' => [
                        ['route' => 'search', 'query_parameters' => ['currentRoute' => 'annotated']],
                    ],
                ],
            ]);
            $menu->addChild('all', [
                'route' => 'all',
                'label' => 'menu.left.all_articles',
                'attributes' => ['class' => 'bold'],
                'linkAttributes' => ['class' => 'waves-effect'],
                'extras' => [
                    'counter' => $this->countEntries('all'),
                    'routes' => [
                        ['route' => 'search', 'query_parameters' => ['currentRoute' => 'all']],
                    ],
                ],
            ]);
        }

        if ($this->authorization->isGranted('LIST_TAGS')) {
            $menu->addChild('tags', [
                'route' => 'tag',
                'label' => 'menu.left.tags',
                'attributes' => ['class' => 'bold'],
                'linkAttributes' => ['class' => 'waves-effect'],
                'extras' => ['counter' => $this->countTags()],
            ]);
        }

        if ($this->authorization->isGranted('ROLE_USER')) {
            $menu->addChild('feeds', [
                'route' => 'all_import_feed',
                'label' => 'feeds.page_title',
                'attributes' => ['class' => 'bold'],
                'linkAttributes' => ['class' => 'waves-effect'],
                'extras' => [
                    'counter' => $this->countPendingFeedItems(),
                    'routes' => ['new_feed', 'edit_feed', 'display_feed_items'],
                ],
            ]);
        }

        return $menu;
    }

    private function countEntries(string $type): int
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (!$user instanceof User) {
            return 0;
        }

        $qb = match ($type) {
            'starred' => $this->entryRepository->getCountBuilderForStarredByUser($user->getId())->select('COUNT(e.id)'),
            'archive' => $this->entryRepository->getCountBuilderForArchiveByUser($user->getId())->select('COUNT(e.id)'),
            'unread' => $this->entryRepository->getCountBuilderForUnreadByUser($user->getId())->select('COUNT(e.id)'),
            'annotated' => $this->annotationRepository->getCountBuilderByUser($user->getId())->select('COUNT(DISTINCT e.entry)'),
            'all' => $this->entryRepository->getCountBuilderForAllByUser($user->getId())->select('COUNT(e.id)'),
            default => throw new \InvalidArgumentException(\sprintf('Type "%s" is not implemented.', $type)),
        };

        return $this->count($qb);
    }

    private function countTags(): int
    {
        $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

        if (!$user instanceof User) {
            return 0;
        }

        return (int) $this->tagRepository->countAllTags($user->getId());
    }

    private function countPendingFeedItems(): int
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        return $user instanceof User ? $this->count($this->feedItemRepository->getPendingCountBuilder($user)) : 0;
    }

    private function count(QueryBuilder $builder): int
    {
        $query = $builder->getQuery();
        $query->useQueryCache(true);
        $query->enableResultCache($this->lifeTime);

        return (int) $query->getSingleScalarResult();
    }
}
