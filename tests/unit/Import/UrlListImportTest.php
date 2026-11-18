<?php

namespace Wallabag\Tests\Unit\Import;

use Doctrine\ORM\EntityManager;
use M6Web\Component\RedisMock\RedisMockFactory;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use Simpleue\Queue\RedisQueue;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Helper\ContentProxy;
use Wallabag\Helper\TagsAssigner;
use Wallabag\Import\UrlListImport;
use Wallabag\Redis\Producer;
use Wallabag\Repository\EntryRepository;

class UrlListImportTest extends TestCase
{
    protected $user;
    protected $em;
    protected $logHandler;
    protected $contentProxy;
    protected $tagsAssigner;

    public function testInit(): void
    {
        $urlListImport = $this->getUrlListImport();

        $this->assertSame('URL list', $urlListImport->getName());
        $this->assertNotEmpty($urlListImport->getUrl());
        $this->assertSame('import.url_list.description', $urlListImport->getDescription());
    }

    public function testImport(): void
    {
        $urlListImport = $this->getUrlListImport(false, 3);
        $urlListImport->setFilepath(__DIR__ . '/../../fixtures/Import/url-list.txt');

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        // comments, empty lines, invalid & duplicated urls are ignored
        $entryRepo->expects($this->exactly(3))
            ->method('findByUrlAndUserId')
            ->willReturn(false);

        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($entryRepo);

        $fetchedUrls = [];
        $this->contentProxy
            ->expects($this->exactly(3))
            ->method('updateEntry')
            ->willReturnCallback(static function (Entry $entry, $url) use (&$fetchedUrls): void {
                $fetchedUrls[] = $url;
            });

        // entries aren't marked as read by default
        $this->em
            ->expects($this->exactly(3))
            ->method('persist')
            ->with($this->callback(static fn ($persistedEntry) => false === $persistedEntry->isArchived()));

        $res = $urlListImport->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 3, 'queued' => 0], $urlListImport->getSummary());
        $this->assertSame([
            'http://youmightnotneedjquery.com/',
            'https://wallabag.org/',
            'https://reporterre.net/Des-abeilles-pour-resoudre-les-conflits-entre-les-humains-et-les-elephants',
        ], $fetchedUrls);
    }

    public function testImportWithByteOrderMarkAndWindowsLineEndings(): void
    {
        $filepath = tempnam(sys_get_temp_dir(), 'wallabag_url_list');
        file_put_contents($filepath, "\xEF\xBB\xBFhttps://example.com/first\r\nhttps://example.com/second\r\n");

        $urlListImport = $this->getUrlListImport(false, 2);
        $urlListImport->setFilepath($filepath);

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(2))
            ->method('findByUrlAndUserId')
            ->willReturn(false);

        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($entryRepo);

        $persistedUrls = [];
        $this->em
            ->expects($this->exactly(2))
            ->method('persist')
            ->willReturnCallback(static function (Entry $entry) use (&$persistedUrls): void {
                $persistedUrls[] = $entry->getUrl();
            });

        try {
            $res = $urlListImport->import();
        } finally {
            unlink($filepath);
        }

        $this->assertTrue($res);
        $this->assertSame(['https://example.com/first', 'https://example.com/second'], $persistedUrls);
    }

    public function testImportAndMarkAllAsRead(): void
    {
        $urlListImport = $this->getUrlListImport(false, 1);
        $urlListImport->setFilepath(__DIR__ . '/../../fixtures/Import/url-list.txt');

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(3))
            ->method('findByUrlAndUserId')
            ->will($this->onConsecutiveCalls(false, true, true));

        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($entryRepo);

        $this->contentProxy
            ->expects($this->exactly(1))
            ->method('updateEntry');

        // check that every entry persisted are archived
        $this->em
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(static fn ($persistedEntry) => (bool) $persistedEntry->isArchived()));

        $res = $urlListImport
            ->setMarkAsRead(true)
            ->import();

        $this->assertTrue($res);

        $this->assertSame(['skipped' => 2, 'imported' => 1, 'queued' => 0], $urlListImport->getSummary());
    }

    public function testImportWithRabbit(): void
    {
        $urlListImport = $this->getUrlListImport();
        $urlListImport->setFilepath(__DIR__ . '/../../fixtures/Import/url-list.txt');

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->never())
            ->method('findByUrlAndUserId');

        $this->em
            ->expects($this->never())
            ->method('getRepository');

        $this->contentProxy
            ->expects($this->never())
            ->method('updateEntry');

        $producer = $this->getMockBuilder(\OldSound\RabbitMqBundle\RabbitMq\Producer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $producer
            ->expects($this->exactly(3))
            ->method('publish');

        $urlListImport->setProducer($producer);

        $res = $urlListImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 3], $urlListImport->getSummary());
    }

    public function testImportWithRedis(): void
    {
        $urlListImport = $this->getUrlListImport();
        $urlListImport->setFilepath(__DIR__ . '/../../fixtures/Import/url-list.txt');

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->never())
            ->method('findByUrlAndUserId');

        $this->em
            ->expects($this->never())
            ->method('getRepository');

        $this->contentProxy
            ->expects($this->never())
            ->method('updateEntry');

        $factory = new RedisMockFactory();
        $redisMock = $factory->getAdapter(Client::class, true);

        $queue = new RedisQueue($redisMock, 'url_list');
        $producer = new Producer($queue);

        $urlListImport->setProducer($producer);

        $res = $urlListImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 3], $urlListImport->getSummary());

        // entries are pushed on the left of the list, the last one is retrieved first
        $message = json_decode($redisMock->lpop('url_list'), true);
        $this->assertSame('https://reporterre.net/Des-abeilles-pour-resoudre-les-conflits-entre-les-humains-et-les-elephants', $message['url']);
        $this->assertTrue($message['is_archived']);
        $this->assertSame($this->user->getId(), $message['userId']);
    }

    public function testValidateEntry(): void
    {
        $urlListImport = $this->getUrlListImport();

        $this->assertTrue($urlListImport->validateEntry(['url' => 'https://example.com/article']));
        $this->assertTrue($urlListImport->validateEntry(['url' => 'HTTP://example.com/']));
        $this->assertTrue($urlListImport->validateEntry(['url' => 'https://example.com/été']));
        $this->assertFalse($urlListImport->validateEntry([]));
        $this->assertFalse($urlListImport->validateEntry(['url' => '']));
        $this->assertFalse($urlListImport->validateEntry(['url' => 'not an url']));
        $this->assertFalse($urlListImport->validateEntry(['url' => 'example.com/article']));
        $this->assertFalse($urlListImport->validateEntry(['url' => 'ftp://example.com/file.txt']));
        $this->assertFalse($urlListImport->validateEntry(['url' => 'javascript:alert(1)']));
        $this->assertFalse($urlListImport->validateEntry(['url' => 'https://']));
    }

    public function testImportFileWithoutUrl(): void
    {
        $urlListImport = $this->getUrlListImport();
        $urlListImport->setFilepath(__DIR__ . '/../../fixtures/Import/test.txt');

        $res = $urlListImport->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('URL list Import: no URL found in imported file', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    public function testImportBadFile(): void
    {
        $urlListImport = $this->getUrlListImport();
        $urlListImport->setFilepath(__DIR__ . '/../../fixtures/Import/wallabag-v1.jsonx');

        $res = $urlListImport->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('URL list Import: unable to read file', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    public function testImportUserNotDefined(): void
    {
        $urlListImport = $this->getUrlListImport(true);
        $urlListImport->setFilepath(__DIR__ . '/../../fixtures/Import/url-list.txt');

        $res = $urlListImport->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('URL list Import: user is not defined', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    private function getUrlListImport($unsetUser = false, $dispatched = 0)
    {
        $this->user = new User();

        $this->em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentProxy = $this->getMockBuilder(ContentProxy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tagsAssigner = $this->getMockBuilder(TagsAssigner::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dispatcher
            ->expects($this->exactly($dispatched))
            ->method('dispatch');

        $this->logHandler = new TestHandler();
        $logger = new Logger('test', [$this->logHandler]);

        $urlListImport = new UrlListImport($this->em, $this->contentProxy, $this->tagsAssigner, $dispatcher, $logger);

        if (false === $unsetUser) {
            $urlListImport->setUser($this->user);
        }

        return $urlListImport;
    }
}
