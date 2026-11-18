<?php

namespace Wallabag\Tests\Unit\Menu;

use Craue\ConfigBundle\Util\Config;
use Knp\Menu\MenuFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Menu\ExportMenuBuilder;

class ExportMenuBuilderTest extends TestCase
{
    /** @dataProvider availability */
    public function testFormatsRequireTheirSettingAndPermission(bool $granted, array $enabled, array $expected): void
    {
        $authorization = $this->createMock(AuthorizationCheckerInterface::class);
        $authorization->method('isGranted')->willReturn($granted);
        $settings = $this->createMock(Config::class);
        $settings->method('get')->willReturnCallback(static fn ($name) => \in_array($name, $enabled, true) ? '1' : '0');
        $builder = new ExportMenuBuilder(new MenuFactory(), $authorization, $settings);

        $this->assertSame($expected, array_keys($builder->createEntryMenu(['entry' => new Entry(new User())])->getChildren()));
        $this->assertSame($expected, array_keys($builder->createListMenu(['category' => 'all', 'filters' => []])->getChildren()));
    }

    public function availability(): iterable
    {
        $allFormats = ['epub', 'pdf', 'csv', 'json', 'txt', 'xml', 'md'];
        $allSettings = array_map(static fn (string $format): string => 'export_' . $format, $allFormats);

        yield 'denied' => [false, $allSettings, []];
        yield 'disabled' => [true, [], []];
        yield 'all formats in order' => [true, $allSettings, $allFormats];
        yield 'independent settings' => [true, ['export_epub', 'export_md'], ['epub', 'md']];

        foreach ($allFormats as $format) {
            yield $format => [true, ['export_' . $format], [$format]];
        }
    }

    public function testEntryMenuRequiresEntryOption(): void
    {
        $builder = new ExportMenuBuilder(
            new MenuFactory(),
            $this->createMock(AuthorizationCheckerInterface::class),
            $this->createMock(Config::class),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The entry option must be an Entry.');

        $builder->createEntryMenu([]);
    }

    public function testListMenuRequiresFiltersOption(): void
    {
        $builder = new ExportMenuBuilder(
            new MenuFactory(),
            $this->createMock(AuthorizationCheckerInterface::class),
            $this->createMock(Config::class),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The category and filters options are required.');

        $builder->createListMenu(['category' => 'all']);
    }
}
