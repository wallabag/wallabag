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
use Wallabag\Import\WallabagV2Import;
use Wallabag\Redis\Producer;
use Wallabag\Repository\EntryRepository;

class WallabagV2ImportTest extends TestCase
{
    protected $user;
    protected $em;
    protected $logHandler;
    protected $contentProxy;
    protected $tagsAssigner;
    protected $uow;

    public function testInit()
    {
        $wallabagV2Import = $this->getWallabagV2Import();

        $this->assertSame('wallabag v2', $wallabagV2Import->getName());
        $this->assertNotEmpty($wallabagV2Import->getUrl());
        $this->assertSame('import.wallabag_v2.description', $wallabagV2Import->getDescription());
    }

    public function testImport()
    {
        $wallabagV2Import = $this->getWallabagV2Import(false, 2);
        $wallabagV2Import->setFilepath(__DIR__ . '/../fixtures/Import/wallabag-v2.json');

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(6))
            ->method('findByUrlAndUserId')
            ->will($this->onConsecutiveCalls(false, true, false));

        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($entryRepo);

        $this->contentProxy
            ->expects($this->exactly(2))
            ->method('updateEntry')
            ->willReturn(new Entry($this->user));

        $res = $wallabagV2Import->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 4, 'imported' => 2, 'queued' => 0], $wallabagV2Import->getSummary());
    }

    public function testImportAndMarkAllAsRead()
    {
        $wallabagV2Import = $this->getWallabagV2Import(false, 2);
        $wallabagV2Import->setFilepath(__DIR__ . '/../fixtures/Import/wallabag-v2-read.json');

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(2))
            ->method('findByUrlAndUserId')
            ->will($this->onConsecutiveCalls(false, false));

        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($entryRepo);

        $this->contentProxy
            ->expects($this->exactly(2))
            ->method('updateEntry')
            ->willReturn(new Entry($this->user));

        // check that every entry persisted are archived
        $this->em
            ->expects($this->any())
            ->method('persist')
            ->with($this->callback(fn ($persistedEntry) => (bool) $persistedEntry->isArchived()));

        $res = $wallabagV2Import->setMarkAsRead(true)->import();

        $this->assertTrue($res);

        $this->assertSame(['skipped' => 0, 'imported' => 2, 'queued' => 0], $wallabagV2Import->getSummary());
    }

    public function testImportWithRabbit()
    {
        $wallabagV2Import = $this->getWallabagV2Import();
        $wallabagV2Import->setFilepath(__DIR__ . '/../fixtures/Import/wallabag-v2.json');

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
            ->expects($this->exactly(6))
            ->method('publish');

        $wallabagV2Import->setProducer($producer);

        $res = $wallabagV2Import->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 6], $wallabagV2Import->getSummary());
    }

    public function testImportWithRedis()
    {
        $wallabagV2Import = $this->getWallabagV2Import();
        $wallabagV2Import->setFilepath(__DIR__ . '/../fixtures/Import/wallabag-v2.json');

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

        $queue = new RedisQueue($redisMock, 'wallabag_v2');
        $producer = new Producer($queue);

        $wallabagV2Import->setProducer($producer);

        $res = $wallabagV2Import->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 6], $wallabagV2Import->getSummary());

        $this->assertNotEmpty($redisMock->lpop('wallabag_v2'));
    }

    public function testImportBadFile()
    {
        $wallabagV1Import = $this->getWallabagV2Import();
        $wallabagV1Import->setFilepath(__DIR__ . '/../fixtures/Import/wallabag-v2.jsonx');

        $res = $wallabagV1Import->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('WallabagImport: unable to read file', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    public function testImportUserNotDefined()
    {
        $wallabagV1Import = $this->getWallabagV2Import(true);
        $wallabagV1Import->setFilepath(__DIR__ . '/../fixtures/Import/wallabag-v2.json');

        $res = $wallabagV1Import->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('WallabagImport: user is not defined', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    public function testImportEmptyFile()
    {
        $wallabagV2Import = $this->getWallabagV2Import();
        $wallabagV2Import->setFilepath(__DIR__ . '/../fixtures/Import/wallabag-v2-empty.json');

        $res = $wallabagV2Import->import();

        $this->assertFalse($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 0], $wallabagV2Import->getSummary());
    }

    public function testImportWithExceptionFromGraby()
    {
        $wallabagV2Import = $this->getWallabagV2Import(false, 2);
        $wallabagV2Import->setFilepath(__DIR__ . '/../fixtures/Import/wallabag-v2.json');

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(6))
            ->method('findByUrlAndUserId')
            ->will($this->onConsecutiveCalls(false, true, false));

        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($entryRepo);

        $this->contentProxy
            ->expects($this->exactly(2))
            ->method('updateEntry')
            ->will($this->throwException(new \Exception()));

        $res = $wallabagV2Import->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 4, 'imported' => 2, 'queued' => 0], $wallabagV2Import->getSummary());
    }

    private function getWallabagV2Import($unsetUser = false, $dispatched = 0)
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

        $wallabag = new WallabagV2Import($this->em, $this->contentProxy, $this->tagsAssigner, $dispatcher, $logger);

        if (false === $unsetUser) {
            $wallabag->setUser($this->user);
        }

        return $wallabag;
    }
}
