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
    public function testRabbitMqImportsTheFeedPublicationDateAndAcceptsOlderMessages(): void
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
        $config->method('get')->with('import_with_rabbitmq')->willReturn('1');
        $producer = $this->createMock(ProducerInterface::class);
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
            $producer,
        );

        self::assertSame(2, $fetcher->fetch($feed));
        self::assertCount(2, $messages);
        $decoded = json_decode($messages[0], true, flags: \JSON_THROW_ON_ERROR);
        self::assertSame($publishedAt->getTimestamp(), $decoded['published_at']);
        self::assertArrayNotHasKey('published_at', json_decode($messages[1], true, flags: \JSON_THROW_ON_ERROR));

        $consumer = $container->get('wallabag.consumer.amqp.feeds');
        foreach ($messages as $message) {
            self::assertTrue($consumer->execute(new AMQPMessage($message)));
        }
        $decoded['published_at'] = $scrapedDate->getTimestamp();
        self::assertTrue($consumer->execute(new AMQPMessage(json_encode($decoded, \JSON_THROW_ON_ERROR))));

        $repository = $em->getRepository(Entry::class);
        $datedEntry = $repository->findOneBy(['user' => $owner->getId(), 'url' => $datedUrl]);
        self::assertSame($publishedAt->getTimestamp(), $datedEntry->getPublishedAt()->getTimestamp());
        self::assertNotSame($publishedAt->getTimestamp(), $datedEntry->getCreatedAt()->getTimestamp());
        $legacyEntry = $repository->findOneBy(['user' => $owner->getId(), 'url' => $legacyUrl]);
        self::assertSame($scrapedDate->getTimestamp(), $legacyEntry->getPublishedAt()->getTimestamp());
    }
}
