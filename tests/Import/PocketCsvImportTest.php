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
use Wallabag\Import\PocketCsvImport;
use Wallabag\Redis\Producer;
use Wallabag\Repository\EntryRepository;

class PocketCsvImportTest extends TestCase
{
    protected $user;
    protected $em;
    protected $logHandler;
    protected $contentProxy;
    protected $tagsAssigner;

    public function testInit()
    {
        $pocketCsvImport = $this->getPocketCsvImport();

        $this->assertSame('Pocket CSV', $pocketCsvImport->getName());
        $this->assertNotEmpty($pocketCsvImport->getUrl());
        $this->assertSame('import.pocket_csv.description', $pocketCsvImport->getDescription());
    }

    public function testImport()
    {
        $pocketCsvImport = $this->getPocketCsvImport(false, 7);
        $pocketCsvImport->setFilepath(__DIR__ . '/../fixtures/Import/pocket.csv');

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(7))
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
            ->expects($this->exactly(7))
            ->method('updateEntry')
            ->willReturn($entry);

        $res = $pocketCsvImport->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 7, 'queued' => 0], $pocketCsvImport->getSummary());
    }

    public function testImportAndMarkAllAsRead()
    {
        $pocketCsvImport = $this->getPocketCsvImport(false, 1);
        $pocketCsvImport->setFilepath(__DIR__ . '/../fixtures/Import/pocket.csv');

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(7))
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

        $res = $pocketCsvImport
            ->setMarkAsRead(true)
            ->import();

        $this->assertTrue($res);

        $this->assertSame(['skipped' => 6, 'imported' => 1, 'queued' => 0], $pocketCsvImport->getSummary());
    }

    public function testImportWithRabbit()
    {
        $pocketCsvImport = $this->getPocketCsvImport();
        $pocketCsvImport->setFilepath(__DIR__ . '/../fixtures/Import/pocket.csv');

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
            ->expects($this->exactly(7))
            ->method('publish');

        $pocketCsvImport->setProducer($producer);

        $res = $pocketCsvImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 7], $pocketCsvImport->getSummary());
    }

    public function testImportWithRedis()
    {
        $pocketCsvImport = $this->getPocketCsvImport();
        $pocketCsvImport->setFilepath(__DIR__ . '/../fixtures/Import/pocket.csv');

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

        $queue = new RedisQueue($redisMock, 'pocket_csv');
        $producer = new Producer($queue);

        $pocketCsvImport->setProducer($producer);

        $res = $pocketCsvImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 7], $pocketCsvImport->getSummary());

        $this->assertNotEmpty($redisMock->lpop('pocket_csv'));
    }

    public function testImportBadFile()
    {
        $pocketCsvImport = $this->getPocketCsvImport();
        $pocketCsvImport->setFilepath(__DIR__ . '/../fixtures/Import/wallabag-v1.jsonx');

        $res = $pocketCsvImport->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('Pocket CSV Import: unable to read file', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    public function testImportUserNotDefined()
    {
        $pocketCsvImport = $this->getPocketCsvImport(true);
        $pocketCsvImport->setFilepath(__DIR__ . '/../fixtures/Import/pocket.csv');

        $res = $pocketCsvImport->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('Pocket CSV Import: user is not defined', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    private function getPocketCsvImport($unsetUser = false, $dispatched = 0)
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

        $wallabag = new PocketCsvImport($this->em, $this->contentProxy, $this->tagsAssigner, $dispatcher, $logger);

        if (false === $unsetUser) {
            $wallabag->setUser($this->user);
        }

        return $wallabag;
    }
}
