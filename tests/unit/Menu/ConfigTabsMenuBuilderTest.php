<?php

namespace Wallabag\Tests\Unit\Menu;

use Knp\Menu\MenuFactory;
use PHPUnit\Framework\TestCase;
use Wallabag\MailImport\MailImportSettings;
use Wallabag\Menu\ConfigTabsMenuBuilder;

class ConfigTabsMenuBuilderTest extends TestCase
{
    public function testAvailableTabs(): void
    {
        $menu = (new ConfigTabsMenuBuilder(new MenuFactory(), new MailImportSettings('', '', 10)))->createMenu([]);

        $this->assertSame(['#set1', '#set2', '#set3', '#set4', '#set5', '#set6', '#set7'], array_values(array_map(static fn ($item) => $item->getUri(), $menu->getChildren())));
    }

    public function testMailImportTab(): void
    {
        $menu = (new ConfigTabsMenuBuilder(new MenuFactory(), new MailImportSettings('wallabag@example.org', 'imaps://user:secret@imap.example.org', 10)))->createMenu([]);

        $this->assertSame('#set8', $menu->getChild('mail_import')->getUri());
    }
}
