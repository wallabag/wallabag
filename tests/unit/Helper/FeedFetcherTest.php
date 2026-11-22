<?php

namespace Wallabag\Tests\Unit\Helper;

use Craue\ConfigBundle\Util\Config;
use Doctrine\ORM\EntityManagerInterface;
use Laminas\Feed\Reader\Entry\EntryInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Wallabag\Entity\FeedItem;
use Wallabag\Entity\FeedUrl;
use Wallabag\Entity\User;
use Wallabag\Helper\ContentProxy;
use Wallabag\Helper\FeedFetcher;
use Wallabag\Helper\FeedReader;
use Wallabag\Helper\TagsAssigner;
use Wallabag\Import\FeedsImport;
use Wallabag\Repository\FeedItemRepository;

class FeedFetcherTest extends TestCase
{
    private const ARTICLE_URL = 'https://example.org/article';

    private EntityManagerInterface&MockObject $entityManager;

    private FeedUrl $feed;

    private ProducerInterface&MockObject $rabbit;

    private ProducerInterface&MockObject $redis;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->rabbit = $this->createMock(ProducerInterface::class);
        $this->redis = $this->createMock(ProducerInterface::class);
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(42);
        $this->feed = (new FeedUrl($user))->setUrl('https://example.org/feed')->setAutomatic(true);
    }

    public function testRabbitMqReceivesTheHistoricalMessageAndTheItemIsRecorded(): void
    {
        $this->rabbit->expects(self::once())->method('publish')->with(self::callback(static function (string $message): bool {
            return ['url' => self::ARTICLE_URL, 'title' => 'Example', 'userId' => 42] === json_decode($message, true);
        }));
        $this->entityManager->expects(self::once())->method('persist')->with(self::callback(
            static fn (FeedItem $item): bool => self::ARTICLE_URL === $item->getUrl() && $item->isTrashed()
        ));

        self::assertSame(1, $this->createFetcher()->fetch($this->feed));
        self::assertNotNull($this->feed->getUpdatedAt());
    }

    public function testPublishFailureDoesNotRecordTheItemOrMarkRefreshSuccessful(): void
    {
        $this->rabbit->expects(self::once())->method('publish')->willThrowException(new \RuntimeException('Broker unavailable'));
        $this->redis->expects(self::never())->method('publish');
        $this->entityManager->expects(self::never())->method('persist');
        $this->entityManager->expects(self::never())->method('flush');

        $this->expectExceptionMessage('Broker unavailable');

        try {
            $this->createFetcher(true, true)->fetch($this->feed);
        } finally {
            self::assertNull($this->feed->getUpdatedAt());
        }
    }

    /** @dataProvider processingModes */
    public function testSelectsConfiguredTransport(bool $rabbit, bool $redis, ?string $expected): void
    {
        $producer = match ($expected) {
            'rabbitmq' => $this->rabbit,
            'redis' => $this->redis,
            default => null,
        };
        $import = $this->createMock(FeedsImport::class);
        $import->expects(self::once())->method('importItem')
            ->with($this->feed->getUser(), self::ARTICLE_URL, 'Example', null, $producer);

        self::assertSame(1, $this->createFetcher($rabbit, $redis, $import)->fetch($this->feed));
    }

    public function processingModes(): iterable
    {
        yield 'synchronous' => [false, false, null];
        yield 'RabbitMQ' => [true, false, 'rabbitmq'];
        yield 'Redis' => [false, true, 'redis'];
        yield 'RabbitMQ takes precedence' => [true, true, 'rabbitmq'];
    }

    private function createFetcher(bool $rabbit = true, bool $redis = false, ?FeedsImport $import = null): FeedFetcher
    {
        $config = $this->createMock(Config::class);
        $config->method('get')->willReturnMap([
            ['import_with_rabbitmq', $rabbit ? '1' : '0'],
            ['import_with_redis', $redis ? '1' : '0'],
        ]);
        $entry = $this->createMock(EntryInterface::class);
        $entry->method('getLink')->willReturn(self::ARTICLE_URL);
        $entry->method('getTitle')->willReturn('Example');
        $reader = $this->createMock(FeedReader::class);
        $reader->method('read')->willReturn(['url' => $this->feed->getUrl(), 'entries' => [$entry]]);
        $items = $this->createMock(FeedItemRepository::class);

        return new FeedFetcher(
            $this->entityManager,
            $items,
            $reader,
            $import ?? new FeedsImport(
                $this->entityManager,
                $this->createMock(ContentProxy::class),
                $this->createMock(TagsAssigner::class),
                $this->createMock(EventDispatcherInterface::class),
                new NullLogger(),
            ),
            $config,
            $this->rabbit,
            $this->redis,
        );
    }
}
