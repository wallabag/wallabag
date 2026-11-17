<?php

namespace Wallabag\Tests\Unit\Menu;

use Craue\ConfigBundle\Util\Config;
use Knp\Menu\MenuFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Menu\EntryShareMenuBuilder;

class EntryShareMenuBuilderTest extends TestCase
{
    /** @dataProvider availability */
    public function testSettingsPermissionsAndPublicUid(array $enabled, array $grants, bool $public, array $expected): void
    {
        $entry = new Entry(new User());
        $entry->setUrl('https://example.com/article?a=1&b=2');
        $entry->setTitle('<b>Example</b>');

        if ($public) {
            $entry->setUid('public-id');
        }

        $authorization = $this->createMock(AuthorizationCheckerInterface::class);
        $authorization->method('isGranted')->willReturnCallback(static fn ($permission) => \in_array($permission, $grants, true));
        $settings = $this->createMock(Config::class);
        $settings->method('get')->willReturnCallback(static fn ($name) => \in_array($name, $enabled, true) ? '1' : '0');
        $menu = (new EntryShareMenuBuilder(new MenuFactory(), $authorization, $settings))->createMenu(['entry' => $entry]);

        $this->assertSame($expected, array_keys($menu->getChildren()));
    }

    /** @dataProvider shaarliOriginUrls */
    public function testShaarliOriginUrl(bool $shareOrigin, ?string $origin, string $expectedQuery): void
    {
        $entry = new Entry(new User());
        $entry->setUrl('https://example.com/article');
        $entry->setOriginUrl($origin);
        $authorization = $this->createMock(AuthorizationCheckerInterface::class);
        $settings = $this->createMock(Config::class);
        $settings->method('get')->willReturnMap([
            ['share_shaarli', '1'],
            ['shaarli_url', 'https://shaarli.example.com'],
            ['shaarli_share_origin_url', $shareOrigin ? '1' : '0'],
        ]);
        $menu = (new EntryShareMenuBuilder(new MenuFactory(), $authorization, $settings))->createMenu(['entry' => $entry]);

        $this->assertSame(
            'https://shaarli.example.com/index.php?post=https%3A%2F%2Fexample.com%2Farticle&title=&tags=' . $expectedQuery,
            $menu->getChild('shaarli')->getUri(),
        );
    }

    public function shaarliOriginUrls(): iterable
    {
        yield 'disabled' => [false, 'https://example.com/?q=é', ''];
        yield 'enabled with origin' => [true, 'https://example.com/?q=é', '&original_url=https%3A%2F%2Fexample.com%2F%3Fq%3D%C3%A9'];
        yield 'enabled without origin' => [true, null, '&original_url='];
    }

    public function availability(): iterable
    {
        yield 'disabled' => [[], ['SHARE', 'UNSHARE'], true, []];
        yield 'public denied' => [['share_public'], [], true, []];
        yield 'share' => [['share_public'], ['SHARE'], false, ['public_link']];
        yield 'unshare needs uid' => [['share_public'], ['UNSHARE'], false, []];
        yield 'unshare' => [['share_public'], ['UNSHARE'], true, ['delete_public_link']];

        foreach (['twitter', 'shaarli', 'diaspora', 'unmark', 'linkding', 'mail'] as $provider) {
            yield $provider => [['share_' . $provider], [], false, [$provider]];
        }
    }

    public function testMissingEntryOption(): void
    {
        $builder = new EntryShareMenuBuilder(
            new MenuFactory(),
            $this->createMock(AuthorizationCheckerInterface::class),
            $this->createMock(Config::class),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The entry option must be an Entry.');

        $builder->createMenu([]);
    }
}
