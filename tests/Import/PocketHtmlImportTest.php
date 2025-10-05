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
use Wallabag\Import\PocketHtmlImport;
use Wallabag\Redis\Producer;
use Wallabag\Repository\EntryRepository;

class PocketHtmlImportTest extends TestCase
{
    protected $user;
    protected $em;
    protected $logHandler;
    protected $contentProxy;
    protected $tagsAssigner;

    public function testInit()
    {
        $pocketHtmlImport = $this->getPocketHtmlImport();

        $this->assertSame('Pocket HTML', $pocketHtmlImport->getName());
        $this->assertNotEmpty($pocketHtmlImport->getUrl());
        $this->assertSame('import.pocket_html.description', $pocketHtmlImport->getDescription());
    }

    public function testImport()
    {
        $pocketHtmlImport = $this->getPocketHtmlImport(false, 2);
        $pocketHtmlImport->setFilepath(__DIR__ . '/../fixtures/Import/ril_export.html');

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

        $entry = $this->getMockBuilder(Entry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentProxy
            ->expects($this->exactly(2))
            ->method('updateEntry')
            ->willReturn($entry);

        $res = $pocketHtmlImport->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 2, 'queued' => 0], $pocketHtmlImport->getSummary());
    }

    public function testImportAndMarkAllAsRead()
    {
        $pocketHtmlImport = $this->getPocketHtmlImport(false, 1);
        $pocketHtmlImport->setFilepath(__DIR__ . '/../fixtures/Import/ril_export.html');

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

        $res = $pocketHtmlImport
            ->setMarkAsRead(true)
            ->import();

        $this->assertTrue($res);

        $this->assertSame(['skipped' => 1, 'imported' => 1, 'queued' => 0], $pocketHtmlImport->getSummary());
    }

    public function testImportWithRabbit()
    {
        $pocketHtmlImport = $this->getPocketHtmlImport();
        $pocketHtmlImport->setFilepath(__DIR__ . '/../fixtures/Import/ril_export.html');

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
            ->expects($this->exactly(2))
            ->method('publish');

        $pocketHtmlImport->setProducer($producer);

        $res = $pocketHtmlImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 2], $pocketHtmlImport->getSummary());
    }

    public function testImportWithRedis()
    {
        $pocketHtmlImport = $this->getPocketHtmlImport();
        $pocketHtmlImport->setFilepath(__DIR__ . '/../fixtures/Import/ril_export.html');

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

        $queue = new RedisQueue($redisMock, 'pocket_html');
        $producer = new Producer($queue);

        $pocketHtmlImport->setProducer($producer);

        $res = $pocketHtmlImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 2], $pocketHtmlImport->getSummary());

        $this->assertNotEmpty($redisMock->lpop('pocket_html'));
    }

    public function testImportBadFile()
    {
        $pocketHtmlImport = $this->getPocketHtmlImport();
        $pocketHtmlImport->setFilepath(__DIR__ . '/../fixtures/Import/wallabag-v1.jsonx');

        $res = $pocketHtmlImport->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('Pocket HTML Import: unable to read file', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    public function testImportUserNotDefined()
    {
        $pocketHtmlImport = $this->getPocketHtmlImport(true);
        $pocketHtmlImport->setFilepath(__DIR__ . '/../fixtures/Import/ril_export.html');

        $res = $pocketHtmlImport->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('Pocket HTML Import: user is not defined', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    private function getPocketHtmlImport($unsetUser = false, $dispatched = 0)
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

        $wallabag = new PocketHtmlImport($this->em, $this->contentProxy, $this->tagsAssigner, $dispatcher, $logger);

        if (false === $unsetUser) {
            $wallabag->setUser($this->user);
        }

        return $wallabag;
    }
}
