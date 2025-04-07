<?php

namespace Tests\Wallabag\Import;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
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
use Wallabag\Import\InstapaperImport;
use Wallabag\Redis\Producer;
use Wallabag\Repository\EntryRepository;

class InstapaperImportTest extends TestCase
{
    protected $user;
    protected $em;
    protected $logHandler;
    protected $contentProxy;
    protected $tagsAssigner;
    protected $uow;

    public function testInit()
    {
        $instapaperImport = $this->getInstapaperImport();

        $this->assertSame('Instapaper', $instapaperImport->getName());
        $this->assertNotEmpty($instapaperImport->getUrl());
        $this->assertSame('import.instapaper.description', $instapaperImport->getDescription());
    }

    public function testImport()
    {
        $instapaperImport = $this->getInstapaperImport(false, 4);
        $instapaperImport->setFilepath(__DIR__ . '/../fixtures/Import/instapaper-export.csv');

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(4))
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
            ->expects($this->exactly(4))
            ->method('updateEntry')
            ->willReturn($entry);

        $res = $instapaperImport->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 4, 'queued' => 0], $instapaperImport->getSummary());
    }

    public function testImportAndMarkAllAsRead()
    {
        $instapaperImport = $this->getInstapaperImport(false, 1);
        $instapaperImport->setFilepath(__DIR__ . '/../fixtures/Import/instapaper-export.csv');

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(4))
            ->method('findByUrlAndUserId')
            ->will($this->onConsecutiveCalls(false, true, true, true));

        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($entryRepo);

        $this->contentProxy
            ->expects($this->once())
            ->method('updateEntry')
            ->willReturn(new Entry($this->user));

        // check that every entry persisted are archived
        $this->em
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(fn ($persistedEntry) => (bool) $persistedEntry->isArchived()));

        $res = $instapaperImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);

        $this->assertSame(['skipped' => 3, 'imported' => 1, 'queued' => 0], $instapaperImport->getSummary());
    }

    public function testImportWithRabbit()
    {
        $instapaperImport = $this->getInstapaperImport();
        $instapaperImport->setFilepath(__DIR__ . '/../fixtures/Import/instapaper-export.csv');

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
            ->expects($this->exactly(4))
            ->method('publish');

        $instapaperImport->setProducer($producer);

        $res = $instapaperImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 4], $instapaperImport->getSummary());
    }

    public function testImportWithRedis()
    {
        $instapaperImport = $this->getInstapaperImport();
        $instapaperImport->setFilepath(__DIR__ . '/../fixtures/Import/instapaper-export.csv');

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

        $queue = new RedisQueue($redisMock, 'instapaper');
        $producer = new Producer($queue);

        $instapaperImport->setProducer($producer);

        $res = $instapaperImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 4], $instapaperImport->getSummary());

        $this->assertNotEmpty($redisMock->lpop('instapaper'));
    }

    public function testImportBadFile()
    {
        $instapaperImport = $this->getInstapaperImport();
        $instapaperImport->setFilepath(__DIR__ . '/../fixtures/Import/wallabag-v1.jsonx');

        $res = $instapaperImport->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('InstapaperImport: unable to read file', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    public function testImportUserNotDefined()
    {
        $instapaperImport = $this->getInstapaperImport(true);
        $instapaperImport->setFilepath(__DIR__ . '/../fixtures/Import/instapaper-export.csv');

        $res = $instapaperImport->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('InstapaperImport: user is not defined', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    private function getInstapaperImport($unsetUser = false, $dispatched = 0)
    {
        $this->user = new User();

        $this->em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->uow = $this->getMockBuilder(UnitOfWork::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->em
            ->expects($this->any())
            ->method('getUnitOfWork')
            ->willReturn($this->uow);

        $this->uow
            ->expects($this->any())
            ->method('getScheduledEntityInsertions')
            ->willReturn([]);

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

        $import = new InstapaperImport($this->em, $this->contentProxy, $this->tagsAssigner, $dispatcher, $logger);

        if (false === $unsetUser) {
            $import->setUser($this->user);
        }

        return $import;
    }
}
