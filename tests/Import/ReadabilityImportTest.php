<?php

namespace Tests\Wallabag\Import;

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
use Wallabag\Import\ReadabilityImport;
use Wallabag\Redis\Producer;
use Wallabag\Repository\EntryRepository;

class ReadabilityImportTest extends TestCase
{
    protected $user;
    protected $em;
    protected $logHandler;
    protected $contentProxy;
    protected $tagsAssigner;

    public function testInit()
    {
        $readabilityImport = $this->getReadabilityImport();

        $this->assertSame('Readability', $readabilityImport->getName());
        $this->assertNotEmpty($readabilityImport->getUrl());
        $this->assertSame('import.readability.description', $readabilityImport->getDescription());
    }

    public function testImport()
    {
        $readabilityImport = $this->getReadabilityImport(false, 3);
        $readabilityImport->setFilepath(__DIR__ . '/../fixtures/Import/readability.json');

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(3))
            ->method('findByUrlAndUserId')
            ->willReturn(false);

        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($entryRepo);

        $entry = $this->getMockBuilder(Entry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentProxy
            ->expects($this->exactly(3))
            ->method('updateEntry')
            ->willReturn($entry);

        $res = $readabilityImport->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 3, 'queued' => 0], $readabilityImport->getSummary());
    }

    public function testImportAndMarkAllAsRead()
    {
        $readabilityImport = $this->getReadabilityImport(false, 1);
        $readabilityImport->setFilepath(__DIR__ . '/../fixtures/Import/readability-read.json');

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(2))
            ->method('findByUrlAndUserId')
            ->will($this->onConsecutiveCalls(false, true));

        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($entryRepo);

        $this->contentProxy
            ->expects($this->exactly(1))
            ->method('updateEntry')
            ->willReturn(new Entry($this->user));

        // check that every entry persisted are archived
        $this->em
            ->expects($this->any())
            ->method('persist')
            ->with($this->callback(fn ($persistedEntry) => (bool) $persistedEntry->isArchived()));

        $res = $readabilityImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);

        $this->assertSame(['skipped' => 1, 'imported' => 1, 'queued' => 0], $readabilityImport->getSummary());
    }

    public function testImportWithRabbit()
    {
        $readabilityImport = $this->getReadabilityImport();
        $readabilityImport->setFilepath(__DIR__ . '/../fixtures/Import/readability.json');

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->never())
            ->method('findByUrlAndUserId');

        $this->em
            ->expects($this->never())
            ->method('getRepository');

        $entry = $this->getMockBuilder(Entry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentProxy
            ->expects($this->never())
            ->method('updateEntry');

        $producer = $this->getMockBuilder(\OldSound\RabbitMqBundle\RabbitMq\Producer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $producer
            ->expects($this->exactly(3))
            ->method('publish');

        $readabilityImport->setProducer($producer);

        $res = $readabilityImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 3], $readabilityImport->getSummary());
    }

    public function testImportWithRedis()
    {
        $readabilityImport = $this->getReadabilityImport();
        $readabilityImport->setFilepath(__DIR__ . '/../fixtures/Import/readability.json');

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->never())
            ->method('findByUrlAndUserId');

        $this->em
            ->expects($this->never())
            ->method('getRepository');

        $entry = $this->getMockBuilder(Entry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentProxy
            ->expects($this->never())
            ->method('updateEntry');

        $factory = new RedisMockFactory();
        $redisMock = $factory->getAdapter(Client::class, true);

        $queue = new RedisQueue($redisMock, 'readability');
        $producer = new Producer($queue);

        $readabilityImport->setProducer($producer);

        $res = $readabilityImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 3], $readabilityImport->getSummary());

        $this->assertNotEmpty($redisMock->lpop('readability'));
    }

    public function testImportBadFile()
    {
        $readabilityImport = $this->getReadabilityImport();
        $readabilityImport->setFilepath(__DIR__ . '/../fixtures/Import/wallabag-v1.jsonx');

        $res = $readabilityImport->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('ReadabilityImport: unable to read file', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    public function testImportUserNotDefined()
    {
        $readabilityImport = $this->getReadabilityImport(true);
        $readabilityImport->setFilepath(__DIR__ . '/../fixtures/Import/readability.json');

        $res = $readabilityImport->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('ReadabilityImport: user is not defined', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    private function getReadabilityImport($unsetUser = false, $dispatched = 0)
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

        $wallabag = new ReadabilityImport($this->em, $this->contentProxy, $this->tagsAssigner, $dispatcher, $logger);

        if (false === $unsetUser) {
            $wallabag->setUser($this->user);
        }

        return $wallabag;
    }
}
