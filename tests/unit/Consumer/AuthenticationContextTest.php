<?php

namespace Wallabag\Tests\Unit\Consumer;

use Doctrine\ORM\EntityManagerInterface;
use Graby\SiteConfig\ConfigBuilder;
use Graby\SiteConfig\SiteConfig;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Wallabag\Consumer\RedisEntryConsumer;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Import\AbstractImport;
use Wallabag\Repository\SiteCredentialRepository;
use Wallabag\Repository\UserRepository;
use Wallabag\SiteConfig\GrabySiteConfigBuilder;

class AuthenticationContextTest extends TestCase
{
    public function testMessagesResolveTheirOwnersCredentialsWithoutSharingSessions(): void
    {
        $users = [];
        foreach ([42, 43] as $id) {
            $user = $this->getMockBuilder(User::class)->disableOriginalConstructor()->getMock();
            $user->method('getId')->willReturn($id);
            $users[$id] = $user;
        }
        $storage = new TokenStorage();
        $browser = new HttpBrowser();
        $browser->getCookieJar()->set(new Cookie('session', 'old-user', null, '/', 'example.org'));
        $credentials = $this->getMockBuilder(SiteCredentialRepository::class)->disableOriginalConstructor()->getMock();
        $credentials->expects(self::exactly(2))->method('findOneByHostsAndUser')
            ->willReturnCallback(static function ($hosts, $id) {
                return ['username' => 'subscriber-' . $id, 'password' => 'test-only'];
            });
        $config = $this->getMockBuilder(ConfigBuilder::class)->disableOriginalConstructor()->getMock();
        $config->method('buildForHost')->willReturn(new SiteConfig());
        $builder = new GrabySiteConfigBuilder($config, $storage, $credentials, new NullLogger());
        $em = $this->createMock(EntityManagerInterface::class);
        $repository = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->getMock();
        $repository->method('find')->willReturnCallback(static fn ($id) => $users[$id]);
        $import = $this->getMockBuilder(AbstractImport::class)->disableOriginalConstructor()->getMockForAbstractClass();
        $import->method('validateEntry')->willReturn(true);
        $import->method('parseEntry')->willReturnCallback(static function ($entry) use ($builder, $users, $browser) {
            self::assertCount(0, $browser->getCookieJar()->all());
            $site = $builder->buildForHost('example.org');
            self::assertNotFalse($site);
            self::assertSame('subscriber-' . $entry['userId'], $site->getUsername());
            $browser->getCookieJar()->set(new Cookie('session', 'current-user', null, '/', 'example.org'));

            return new Entry($users[$entry['userId']]);
        });
        $consumer = new RedisEntryConsumer($em, $repository, $import, new EventDispatcher(), null, $storage, $browser);
        foreach ([42, 43] as $id) {
            self::assertTrue($consumer->manage(json_encode(['userId' => $id, 'url' => 'https://example.org/article'])));
            self::assertNull($storage->getToken());
            self::assertCount(0, $browser->getCookieJar()->all());
        }
    }

    public function testExtractionFailureRestoresContextAndClearsCookies(): void
    {
        $user = new User();
        $storage = new TokenStorage();
        $original = new UsernamePasswordToken(new User(), 'main', ['ROLE_USER']);
        $storage->setToken($original);
        $browser = new HttpBrowser();
        $em = $this->createMock(EntityManagerInterface::class);
        $repository = $this->getMockBuilder(UserRepository::class)->disableOriginalConstructor()->getMock();
        $repository->method('find')->willReturn($user);
        $import = $this->getMockBuilder(AbstractImport::class)->disableOriginalConstructor()->getMockForAbstractClass();
        $import->method('validateEntry')->willReturn(true);
        $import->method('parseEntry')->willReturnCallback(static function () use ($user, $storage, $browser) {
            self::assertSame($user, $storage->getToken()->getUser());
            $browser->getCookieJar()->set(new Cookie('session', 'current-user', null, '/', 'example.org'));
            throw new \RuntimeException('simulated extraction failure');
        });
        $consumer = new RedisEntryConsumer($em, $repository, $import, new EventDispatcher(), null, $storage, $browser);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('simulated extraction failure');
        try {
            $consumer->manage(json_encode(['userId' => 1, 'url' => 'https://example.org/article']));
        } finally {
            self::assertSame($original, $storage->getToken());
            self::assertCount(0, $browser->getCookieJar()->all());
        }
    }
}
