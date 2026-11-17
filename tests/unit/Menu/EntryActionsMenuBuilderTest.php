<?php

namespace Wallabag\Tests\Unit\Menu;

use Knp\Menu\MenuFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Menu\EntryActionsMenuBuilder;

class EntryActionsMenuBuilderTest extends TestCase
{
    /** @dataProvider permissions */
    public function testActionsAreOnlyAddedWhenGranted(array $grants, array $expected): void
    {
        $entry = new Entry(new User());
        $authorization = $this->createMock(AuthorizationCheckerInterface::class);
        $authorization->method('isGranted')->willReturnCallback(static fn ($permission, $subject) => $subject === $entry && \in_array($permission, $grants, true));
        $builder = new EntryActionsMenuBuilder(new MenuFactory(), $authorization);
        $menu = $builder->createMenu(['entry' => $entry, 'redirect' => '/search?search_entry[term]=hello&page=2']);

        $this->assertSame($expected, array_keys($menu->getChildren()));

        foreach ($menu->getChildren() as $name => $item) {
            $this->assertSame('form', $item->getExtra('kind'));
            $this->assertSame($name . '-entry', $item->getExtra('csrf_token_id'));
        }
    }

    /** @dataProvider states */
    public function testActionLabelsDescribeTheAvailableAction(bool $archived, bool $starred, string $archiveLabel, string $starLabel): void
    {
        $entry = new Entry(new User());
        $entry->setArchived($archived);
        $entry->setStarred($starred);
        $authorization = $this->createMock(AuthorizationCheckerInterface::class);
        $authorization->method('isGranted')->willReturn(true);
        $menu = (new EntryActionsMenuBuilder(new MenuFactory(), $authorization))->createMenu(['entry' => $entry, 'redirect' => '/view/1']);

        $this->assertSame($archiveLabel, $menu->getChild('archive')->getLabel());
        $this->assertSame($archiveLabel, $menu->getChild('archive')->getLinkAttribute('title'));
        $this->assertSame($starLabel, $menu->getChild('star')->getLabel());
        $this->assertSame($starLabel, $menu->getChild('star')->getLinkAttribute('title'));
        $this->assertSame('entry.confirm.delete', $menu->getChild('delete')->getExtra('confirmation'));
    }

    public function states(): iterable
    {
        yield 'unread and not starred' => [false, false, 'entry.action.set_as_read', 'entry.action.star'];
        yield 'archived and starred' => [true, true, 'entry.action.set_as_unread', 'entry.action.unstar'];
        yield 'unread and starred' => [false, true, 'entry.action.set_as_read', 'entry.action.unstar'];
        yield 'archived and not starred' => [true, false, 'entry.action.set_as_unread', 'entry.action.star'];
    }

    public function permissions(): iterable
    {
        yield 'none' => [[], []];
        yield 'archive' => [['ARCHIVE'], ['archive']];
        yield 'star' => [['STAR'], ['star']];
        yield 'delete' => [['DELETE'], ['delete']];
        yield 'all' => [['ARCHIVE', 'STAR', 'DELETE'], ['archive', 'star', 'delete']];
    }

    public function testMissingRedirectOption(): void
    {
        $builder = new EntryActionsMenuBuilder(
            new MenuFactory(),
            $this->createMock(AuthorizationCheckerInterface::class),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The entry and redirect options are required.');

        $builder->createMenu(['entry' => new Entry(new User())]);
    }
}
