<?php

namespace Wallabag\Tests\Integration\Consumer;

use Craue\ConfigBundle\Util\Config;
use Laminas\Feed\Reader\Entry\EntryInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Wallabag\Entity\Entry;
use Wallabag\Entity\FeedItem;
use Wallabag\Entity\FeedUrl;
use Wallabag\Helper\ContentProxy;
use Wallabag\Helper\FeedFetcher;
use Wallabag\Helper\FeedReader;
use Wallabag\Import\FeedsImport;
use Wallabag\Tests\Integration\WallabagKernelTestCase;

class FeedPublicationDateTest extends WallabagKernelTestCase
{
    /** @dataProvider transports */
    public function testQueuedImportPreservesTheFeedPublicationDateAndAcceptsOlderMessages(string $transport): void
    {
        $em = $this->getEntityManager();
        $owner = $this->getUser('admin');
        $feed = (new FeedUrl($owner))->setTitle('Queued')->setUrl('https://example.org/feed')->setAutomatic(true);
        $em->persist($feed);
        $em->flush();

        $datedUrl = 'https://example.org/dated-article';
        $legacyUrl = 'https://example.org/legacy-article';
        $publishedAt = new \DateTime('2020-06-01T13:00:00+01:00');
        $dated = $this->createMock(EntryInterface::class);
        $dated->method('getLink')->willReturn($datedUrl);
        $dated->method('getDateCreated')->willReturn($publishedAt);
        $legacy = $this->createMock(EntryInterface::class);
        $legacy->method('getLink')->willReturn($legacyUrl);

        $reader = $this->createMock(FeedReader::class);
        $reader->method('read')->willReturn(['url' => $feed->getUrl(), 'entries' => [$dated, $legacy]]);
        $config = $this->createMock(Config::class);
        $config->method('get')->willReturnMap([
            ['import_with_rabbitmq', 'amqp' === $transport ? '1' : '0'],
            ['import_with_redis', 'redis' === $transport ? '1' : '0'],
        ]);
        $producer = $this->createMock(ProducerInterface::class);
        $unusedProducer = $this->createMock(ProducerInterface::class);
        $unusedProducer->expects(self::never())->method('publish');
        /** @var list<string> $messages */
        $messages = [];
        $producer->expects(self::exactly(2))->method('publish')->willReturnCallback(static function (string $body) use (&$messages): void {
            $messages[] = $body;
        });

        $scrapedDate = new \DateTime('2021-06-01 12:00:00');
        $content = $this->createMock(ContentProxy::class);
        $content->method('updateEntry')->willReturnCallback(static function (Entry $entry) use ($scrapedDate): void {
            $entry->setPublishedAt($scrapedDate);
        });
        $container = static::getContainer();
        $container->set(ContentProxy::class, $content);
        $fetcher = new FeedFetcher(
            $em,
            $em->getRepository(FeedItem::class),
            $reader,
            $container->get(FeedsImport::class),
            $config,
            'amqp' === $transport ? $producer : $unusedProducer,
            'redis' === $transport ? $producer : $unusedProducer,
        );

        self::assertSame(2, $fetcher->fetch($feed));
        self::assertCount(2, $messages);
        $decoded = json_decode($messages[0], true, flags: \JSON_THROW_ON_ERROR);
        self::assertSame($publishedAt->getTimestamp(), $decoded['published_at']);
        self::assertArrayNotHasKey('published_at', json_decode($messages[1], true, flags: \JSON_THROW_ON_ERROR));

        $consumer = $container->get('wallabag.consumer.' . $transport . '.feeds');
        $consume = 'amqp' === $transport
            ? static fn (string $message) => $consumer->execute(new AMQPMessage($message))
            : static fn (string $message) => $consumer->manage($message);

        foreach ($messages as $message) {
            self::assertTrue($consume($message));
        }
        $decoded['published_at'] = $scrapedDate->getTimestamp();
        self::assertTrue($consume(json_encode($decoded, \JSON_THROW_ON_ERROR)));

        $repository = $em->getRepository(Entry::class);
        $datedEntry = $repository->findOneBy(['user' => $owner->getId(), 'url' => $datedUrl]);
        self::assertSame($publishedAt->getTimestamp(), $datedEntry->getPublishedAt()->getTimestamp());
        self::assertNotSame($publishedAt->getTimestamp(), $datedEntry->getCreatedAt()->getTimestamp());
        $legacyEntry = $repository->findOneBy(['user' => $owner->getId(), 'url' => $legacyUrl]);
        self::assertSame($scrapedDate->getTimestamp(), $legacyEntry->getPublishedAt()->getTimestamp());
    }

    public function transports(): iterable
    {
        yield 'RabbitMQ' => ['amqp'];
        yield 'Redis' => ['redis'];
    }
}
