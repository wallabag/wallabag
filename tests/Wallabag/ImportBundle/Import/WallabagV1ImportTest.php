<?php

namespace Tests\Wallabag\ImportBundle\Import;

use M6Web\Component\RedisMock\RedisMockFactory;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Simpleue\Queue\RedisQueue;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\ImportBundle\Import\WallabagV1Import;
use Wallabag\ImportBundle\Redis\Producer;
use Wallabag\UserBundle\Entity\User;

class WallabagV1ImportTest extends TestCase
{
    protected $user;
    protected $em;
    protected $logHandler;
    protected $contentProxy;
    protected $tagsAssigner;
    protected $uow;
    protected $fetchingErrorMessageTitle = 'No title found';
    protected $fetchingErrorMessage = 'wallabag can\'t retrieve contents for this article. Please <a href="http://doc.wallabag.org/en/master/user/errors_during_fetching.html#how-can-i-help-to-fix-that">troubleshoot this issue</a>.';

    public function testInit()
    {
        $wallabagV1Import = $this->getWallabagV1Import();

        $this->assertSame('wallabag v1', $wallabagV1Import->getName());
        $this->assertNotEmpty($wallabagV1Import->getUrl());
        $this->assertSame('import.wallabag_v1.description', $wallabagV1Import->getDescription());
    }

    public function testImport()
    {
        $wallabagV1Import = $this->getWallabagV1Import(false, 1);
        $wallabagV1Import->setFilepath(__DIR__ . '/../fixtures/wallabag-v1.json');

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(2))
            ->method('findByUrlAndUserId')
            ->will($this->onConsecutiveCalls(false, true, false, false));

        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($entryRepo);

        $entry = $this->getMockBuilder('Wallabag\CoreBundle\Entity\Entry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentProxy
            ->expects($this->exactly(1))
            ->method('updateEntry')
            ->willReturn($entry);

        $res = $wallabagV1Import->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 1, 'imported' => 1, 'queued' => 0], $wallabagV1Import->getSummary());
    }

    public function testImportAndMarkAllAsRead()
    {
        $wallabagV1Import = $this->getWallabagV1Import(false, 3);
        $wallabagV1Import->setFilepath(__DIR__ . '/../fixtures/wallabag-v1-read.json');

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(3))
            ->method('findByUrlAndUserId')
            ->will($this->onConsecutiveCalls(false, false, false));

        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($entryRepo);

        $this->contentProxy
            ->expects($this->exactly(3))
            ->method('updateEntry')
            ->willReturn(new Entry($this->user));

        // check that every entry persisted are archived
        $this->em
            ->expects($this->any())
            ->method('persist')
            ->with($this->callback(function ($persistedEntry) {
                return (bool) $persistedEntry->isArchived();
            }));

        $res = $wallabagV1Import->setMarkAsRead(true)->import();

        $this->assertTrue($res);

        $this->assertSame(['skipped' => 0, 'imported' => 3, 'queued' => 0], $wallabagV1Import->getSummary());
    }

    public function testImportWithRabbit()
    {
        $wallabagV1Import = $this->getWallabagV1Import();
        $wallabagV1Import->setFilepath(__DIR__ . '/../fixtures/wallabag-v1.json');

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->never())
            ->method('findByUrlAndUserId');

        $this->em
            ->expects($this->never())
            ->method('getRepository');

        $entry = $this->getMockBuilder('Wallabag\CoreBundle\Entity\Entry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentProxy
            ->expects($this->never())
            ->method('updateEntry');

        $producer = $this->getMockBuilder('OldSound\RabbitMqBundle\RabbitMq\Producer')
            ->disableOriginalConstructor()
            ->getMock();

        $producer
            ->expects($this->exactly(2))
            ->method('publish');

        $wallabagV1Import->setProducer($producer);

        $res = $wallabagV1Import->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 2], $wallabagV1Import->getSummary());
    }

    public function testImportWithRedis()
    {
        $wallabagV1Import = $this->getWallabagV1Import();
        $wallabagV1Import->setFilepath(__DIR__ . '/../fixtures/wallabag-v1.json');

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->never())
            ->method('findByUrlAndUserId');

        $this->em
            ->expects($this->never())
            ->method('getRepository');

        $entry = $this->getMockBuilder('Wallabag\CoreBundle\Entity\Entry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentProxy
            ->expects($this->never())
            ->method('updateEntry');

        $factory = new RedisMockFactory();
        $redisMock = $factory->getAdapter('Predis\Client', true);

        $queue = new RedisQueue($redisMock, 'wallabag_v1');
        $producer = new Producer($queue);

        $wallabagV1Import->setProducer($producer);

        $res = $wallabagV1Import->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 2], $wallabagV1Import->getSummary());

        $this->assertNotEmpty($redisMock->lpop('wallabag_v1'));
    }

    public function testImportBadFile()
    {
        $wallabagV1Import = $this->getWallabagV1Import();
        $wallabagV1Import->setFilepath(__DIR__ . '/../fixtures/wallabag-v1.jsonx');

        $res = $wallabagV1Import->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('WallabagImport: unable to read file', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    public function testImportUserNotDefined()
    {
        $wallabagV1Import = $this->getWallabagV1Import(true);
        $wallabagV1Import->setFilepath(__DIR__ . '/../fixtures/wallabag-v1.json');

        $res = $wallabagV1Import->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('WallabagImport: user is not defined', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    private function getWallabagV1Import($unsetUser = false, $dispatched = 0)
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

        $wallabag = new WallabagV1Import(
            $this->em,
            $this->contentProxy,
            $this->tagsAssigner,
            $dispatcher,
            $this->fetchingErrorMessageTitle,
            $this->fetchingErrorMessage
        );

        $this->logHandler = new TestHandler();
        $logger = new Logger('test', [$this->logHandler]);
        $wallabag->setLogger($logger);

        if (false === $unsetUser) {
            $wallabag->setUser($this->user);
        }

        return $wallabag;
    }
}
