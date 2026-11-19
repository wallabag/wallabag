<?php

namespace Wallabag\Tests\Unit\Command;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Wallabag\Command\FetchRssCommand;
use Wallabag\Entity\FeedUrl;
use Wallabag\Entity\User;
use Wallabag\Helper\FeedFetcher;
use Wallabag\Repository\FeedUrlRepository;

class FetchRssCommandTest extends TestCase
{
    public function testRestoresContextAndContinuesAfterFailure(): void
    {
        $user = new User();
        $feed = (new FeedUrl($user))->setTitle('Example')->setUrl('https://example.org/feed');
        $feeds = $this->createMock(FeedUrlRepository::class);
        $feeds->method('iterateAll')->willReturn([$feed, $feed]);
        $fetcher = $this->createMock(FeedFetcher::class);
        $fetcher->expects(self::exactly(2))->method('fetch')->willReturnOnConsecutiveCalls(self::throwException(new \RuntimeException('failed')), 1);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('isOpen')->willReturn(true);
        $tokens = new TokenStorage();
        $previous = new UsernamePasswordToken($user, 'main', []);
        $tokens->setToken($previous);
        $browser = new HttpBrowser(new MockHttpClient());
        $browser->getCookieJar()->set(new Cookie('session', 'original', null, '/', 'example.org'));
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with('Unable to refresh feed subscription.', [
            'feed_id' => $feed->getId(),
            'exception_type' => \RuntimeException::class,
        ]);
        $tester = new CommandTester(new FetchRssCommand($feeds, $fetcher, $em, $tokens, $browser, $logger));

        self::assertSame(Command::FAILURE, $tester->execute([]));
        self::assertSame($previous, $tokens->getToken());
        self::assertSame('original', $browser->getCookieJar()->get('session', '/', 'example.org')->getValue());
        self::assertStringContainsString('Unable to refresh feed', $tester->getDisplay());
        self::assertStringContainsString('1 new items', $tester->getDisplay());
        self::assertStringContainsString('Successful subscriptions: 1. Failed subscriptions: 1. New items: 1.', $tester->getDisplay());
    }

    public function testUsersDoNotShareBrowserCookies(): void
    {
        $first = new User();
        $second = new User();
        $feeds = $this->createMock(FeedUrlRepository::class);
        $feeds->method('iterateAll')->willReturn([new FeedUrl($first), new FeedUrl($second)]);
        $tokens = new TokenStorage();
        $browser = new HttpBrowser(new MockHttpClient());
        $fetcher = $this->createMock(FeedFetcher::class);
        $fetcher->expects(self::exactly(2))->method('fetch')->willReturnCallback(static function (FeedUrl $feed) use ($tokens, $browser): int {
            self::assertSame($feed->getUser(), $tokens->getToken()->getUser());
            self::assertNull($browser->getCookieJar()->get('session', '/', 'example.org'));
            $browser->getCookieJar()->set(new Cookie('session', 'private', null, '/', 'example.org'));

            return 0;
        });
        $tester = new CommandTester(new FetchRssCommand($feeds, $fetcher, $this->createMock(EntityManagerInterface::class), $tokens, $browser, new NullLogger()));

        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertNull($tokens->getToken());
        self::assertNull($browser->getCookieJar()->get('session', '/', 'example.org'));
    }

    public function testStopsWhenTheEntityManagerCloses(): void
    {
        $feed = new FeedUrl(new User());
        $feeds = $this->createMock(FeedUrlRepository::class);
        $feeds->method('iterateAll')->willReturn([$feed, $feed]);
        $fetcher = $this->createMock(FeedFetcher::class);
        $fetcher->expects(self::once())->method('fetch')->willThrowException(new \RuntimeException('Database failure'));
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('isOpen')->willReturn(false);
        $tokens = new TokenStorage();
        $browser = new HttpBrowser(new MockHttpClient());
        $tester = new CommandTester(new FetchRssCommand($feeds, $fetcher, $em, $tokens, $browser, new NullLogger()));

        self::assertSame(Command::FAILURE, $tester->execute([]));
        self::assertNull($tokens->getToken());
        self::assertStringContainsString('Stopped processing subscriptions', $tester->getDisplay());
        self::assertStringContainsString('Successful subscriptions: 0. Failed subscriptions: 1. New items: 0.', $tester->getDisplay());
    }
}
