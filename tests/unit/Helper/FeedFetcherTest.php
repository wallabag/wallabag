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

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->rabbit = $this->createMock(ProducerInterface::class);
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
        $this->entityManager->expects(self::never())->method('persist');
        $this->entityManager->expects(self::never())->method('flush');

        $this->expectExceptionMessage('Broker unavailable');
        try {
            $this->createFetcher()->fetch($this->feed);
        } finally {
            self::assertNull($this->feed->getUpdatedAt());
        }
    }

    private function createFetcher(): FeedFetcher
    {
        $config = $this->createMock(Config::class);
        $config->method('get')->with('import_with_rabbitmq')->willReturn('1');
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
            new FeedsImport(
                $this->entityManager,
                $this->createMock(ContentProxy::class),
                $this->createMock(TagsAssigner::class),
                $this->createMock(EventDispatcherInterface::class),
                new NullLogger(),
            ),
            $config,
            $this->rabbit,
        );
    }
}
