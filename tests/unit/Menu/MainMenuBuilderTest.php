<?php

namespace Wallabag\Tests\Unit\Menu;

use Knp\Menu\Matcher\Voter\RouteVoter;
use Knp\Menu\MenuFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Wallabag\Menu\MainMenuBuilder;
use Wallabag\Repository\AnnotationRepository;
use Wallabag\Repository\EntryRepository;
use Wallabag\Repository\FeedItemRepository;
use Wallabag\Repository\TagRepository;

class MainMenuBuilderTest extends TestCase
{
    /** @dataProvider permissions */
    public function testOnlyGrantedSectionsAreAdded(array $grants, array $expected): void
    {
        $authorization = $this->createMock(AuthorizationCheckerInterface::class);
        $authorization->method('isGranted')->willReturnCallback(static fn ($permission) => \in_array($permission, $grants, true));
        $builder = new MainMenuBuilder(
            new MenuFactory(),
            $authorization,
            $this->createMock(TokenStorageInterface::class),
            $this->createMock(EntryRepository::class),
            $this->createMock(AnnotationRepository::class),
            $this->createMock(TagRepository::class),
            60,
            $this->createMock(FeedItemRepository::class),
        );

        $menu = $builder->createMenu([]);
        $this->assertSame($expected, array_keys($menu->getChildren()));

        foreach ($menu->getChildren() as $item) {
            $this->assertSame(0, $item->getExtra('counter'));
        }
    }

    public function testSearchMatchesItsOriginatingList(): void
    {
        $factory = new MenuFactory();
        $authorization = $this->createMock(AuthorizationCheckerInterface::class);
        $authorization->method('isGranted')->willReturn(true);
        $builder = new MainMenuBuilder(
            $factory,
            $authorization,
            $this->createMock(TokenStorageInterface::class),
            $this->createMock(EntryRepository::class),
            $this->createMock(AnnotationRepository::class),
            $this->createMock(TagRepository::class),
            0,
            $this->createMock(FeedItemRepository::class),
        );
        $menu = $builder->createMenu([]);
        $stack = new RequestStack();
        $voter = new RouteVoter($stack);

        foreach (['homepage' => 'unread', 'unread' => 'unread', 'starred' => 'starred', 'archive' => 'archive', 'annotated' => 'annotated', 'all' => 'all', 'untagged' => null] as $route => $expected) {
            $stack->push(new Request(['currentRoute' => $route], [], ['_route' => 'search']));

            foreach ($menu->getChildren() as $name => $item) {
                $this->assertSame($name === $expected ? true : null, $voter->matchItem($item), $route . ' / ' . $name);
            }

            $stack->pop();
        }
    }

    public function permissions(): iterable
    {
        yield 'none' => [[], []];
        yield 'tags only' => [['LIST_TAGS'], ['tags']];
        yield 'entries only' => [['LIST_ENTRIES'], ['unread', 'starred', 'archive', 'annotated', 'all']];
        yield 'signed-in user' => [['ROLE_USER'], ['feeds']];
    }
}
