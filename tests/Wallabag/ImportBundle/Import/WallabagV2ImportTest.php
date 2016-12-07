<?php

namespace Tests\Wallabag\ImportBundle\Import;

use Wallabag\ImportBundle\Import\WallabagV2Import;
use Wallabag\UserBundle\Entity\User;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\ImportBundle\Redis\Producer;
use Monolog\Logger;
use Monolog\Handler\TestHandler;
use Simpleue\Queue\RedisQueue;
use M6Web\Component\RedisMock\RedisMockFactory;

class WallabagV2ImportTest extends \PHPUnit_Framework_TestCase
{
    protected $user;
    protected $em;
    protected $logHandler;
    protected $contentProxy;
    protected $tagsAssigner;
    protected $uow;

    private function getWallabagV2Import($unsetUser = false, $dispatched = 0)
    {
        $this->user = new User();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->uow = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')
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

        $this->contentProxy = $this->getMockBuilder('Wallabag\CoreBundle\Helper\ContentProxy')
            ->disableOriginalConstructor()
            ->getMock();

        $this->tagsAssigner = $this->getMockBuilder('Wallabag\CoreBundle\Helper\TagsAssigner')
            ->disableOriginalConstructor()
            ->getMock();

        $dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
            ->disableOriginalConstructor()
            ->getMock();

        $dispatcher
            ->expects($this->exactly($dispatched))
            ->method('dispatch');

        $wallabag = new WallabagV2Import($this->em, $this->contentProxy, $this->tagsAssigner, $dispatcher);

        $this->logHandler = new TestHandler();
        $logger = new Logger('test', [$this->logHandler]);
        $wallabag->setLogger($logger);

        if (false === $unsetUser) {
            $wallabag->setUser($this->user);
        }

        return $wallabag;
    }

    public function testInit()
    {
        $wallabagV2Import = $this->getWallabagV2Import();

        $this->assertEquals('wallabag v2', $wallabagV2Import->getName());
        $this->assertNotEmpty($wallabagV2Import->getUrl());
        $this->assertEquals('import.wallabag_v2.description', $wallabagV2Import->getDescription());
    }

    public function testImport()
    {
        $wallabagV2Import = $this->getWallabagV2Import(false, 2);
        $wallabagV2Import->setFilepath(__DIR__.'/../fixtures/wallabag-v2.json');

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
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
            ->method('importEntry')
            ->willReturn(new Entry($this->user));

        $res = $wallabagV2Import->import();

        $this->assertTrue($res);
        $this->assertEquals(['skipped' => 4, 'imported' => 2, 'queued' => 0], $wallabagV2Import->getSummary());
    }

    public function testImportAndMarkAllAsRead()
    {
        $wallabagV2Import = $this->getWallabagV2Import(false, 2);
        $wallabagV2Import->setFilepath(__DIR__.'/../fixtures/wallabag-v2-read.json');

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
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
            ->method('importEntry')
            ->willReturn(new Entry($this->user));

        // check that every entry persisted are archived
        $this->em
            ->expects($this->any())
            ->method('persist')
            ->with($this->callback(function ($persistedEntry) {
                return $persistedEntry->isArchived();
            }));

        $res = $wallabagV2Import->setMarkAsRead(true)->import();

        $this->assertTrue($res);

        $this->assertEquals(['skipped' => 0, 'imported' => 2, 'queued' => 0], $wallabagV2Import->getSummary());
    }

    public function testImportWithRabbit()
    {
        $wallabagV2Import = $this->getWallabagV2Import();
        $wallabagV2Import->setFilepath(__DIR__.'/../fixtures/wallabag-v2.json');

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->never())
            ->method('findByUrlAndUserId');

        $this->em
            ->expects($this->never())
            ->method('getRepository');

        $this->contentProxy
            ->expects($this->never())
            ->method('importEntry');

        $producer = $this->getMockBuilder('OldSound\RabbitMqBundle\RabbitMq\Producer')
            ->disableOriginalConstructor()
            ->getMock();

        $producer
            ->expects($this->exactly(6))
            ->method('publish');

        $wallabagV2Import->setProducer($producer);

        $res = $wallabagV2Import->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertEquals(['skipped' => 0, 'imported' => 0, 'queued' => 6], $wallabagV2Import->getSummary());
    }

    public function testImportWithRedis()
    {
        $wallabagV2Import = $this->getWallabagV2Import();
        $wallabagV2Import->setFilepath(__DIR__.'/../fixtures/wallabag-v2.json');

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->never())
            ->method('findByUrlAndUserId');

        $this->em
            ->expects($this->never())
            ->method('getRepository');

        $this->contentProxy
            ->expects($this->never())
            ->method('importEntry');

        $factory = new RedisMockFactory();
        $redisMock = $factory->getAdapter('Predis\Client', true);

        $queue = new RedisQueue($redisMock, 'wallabag_v2');
        $producer = new Producer($queue);

        $wallabagV2Import->setProducer($producer);

        $res = $wallabagV2Import->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertEquals(['skipped' => 0, 'imported' => 0, 'queued' => 6], $wallabagV2Import->getSummary());

        $this->assertNotEmpty($redisMock->lpop('wallabag_v2'));
    }

    public function testImportBadFile()
    {
        $wallabagV1Import = $this->getWallabagV2Import();
        $wallabagV1Import->setFilepath(__DIR__.'/../fixtures/wallabag-v2.jsonx');

        $res = $wallabagV1Import->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertContains('WallabagImport: unable to read file', $records[0]['message']);
        $this->assertEquals('ERROR', $records[0]['level_name']);
    }

    public function testImportUserNotDefined()
    {
        $wallabagV1Import = $this->getWallabagV2Import(true);
        $wallabagV1Import->setFilepath(__DIR__.'/../fixtures/wallabag-v2.json');

        $res = $wallabagV1Import->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertContains('WallabagImport: user is not defined', $records[0]['message']);
        $this->assertEquals('ERROR', $records[0]['level_name']);
    }

    public function testImportEmptyFile()
    {
        $wallabagV2Import = $this->getWallabagV2Import();
        $wallabagV2Import->setFilepath(__DIR__.'/../fixtures/wallabag-v2-empty.json');

        $res = $wallabagV2Import->import();

        $this->assertFalse($res);
        $this->assertEquals(['skipped' => 0, 'imported' => 0, 'queued' => 0], $wallabagV2Import->getSummary());
    }

    public function testImportWithExceptionFromGraby()
    {
        $wallabagV2Import = $this->getWallabagV2Import(false, 2);
        $wallabagV2Import->setFilepath(__DIR__.'/../fixtures/wallabag-v2.json');

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
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
            ->method('importEntry')
            ->will($this->throwException(new \Exception()));

        $res = $wallabagV2Import->import();

        $this->assertTrue($res);
        $this->assertEquals(['skipped' => 4, 'imported' => 2, 'queued' => 0], $wallabagV2Import->getSummary());
    }
}
