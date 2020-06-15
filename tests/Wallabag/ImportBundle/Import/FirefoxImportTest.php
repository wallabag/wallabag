<?php

namespace Tests\Wallabag\ImportBundle\Import;

use M6Web\Component\RedisMock\RedisMockFactory;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Simpleue\Queue\RedisQueue;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\ImportBundle\Import\FirefoxImport;
use Wallabag\ImportBundle\Redis\Producer;
use Wallabag\UserBundle\Entity\User;

class FirefoxImportTest extends TestCase
{
    protected $user;
    protected $em;
    protected $logHandler;
    protected $contentProxy;
    protected $tagsAssigner;

    public function testInit()
    {
        $firefoxImport = $this->getFirefoxImport();

        $this->assertSame('Firefox', $firefoxImport->getName());
        $this->assertNotEmpty($firefoxImport->getUrl());
        $this->assertSame('import.firefox.description', $firefoxImport->getDescription());
    }

    public function testImport()
    {
        $firefoxImport = $this->getFirefoxImport(false, 2);
        $firefoxImport->setFilepath(__DIR__ . '/../fixtures/firefox-bookmarks.json');

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(2))
            ->method('findByUrlAndUserId')
            ->willReturn(false);

        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($entryRepo);

        $entry = $this->getMockBuilder('Wallabag\CoreBundle\Entity\Entry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentProxy
            ->expects($this->exactly(2))
            ->method('updateEntry')
            ->willReturn($entry);

        $res = $firefoxImport->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 2, 'queued' => 0], $firefoxImport->getSummary());
    }

    public function testImportAndMarkAllAsRead()
    {
        $firefoxImport = $this->getFirefoxImport(false, 1);
        $firefoxImport->setFilepath(__DIR__ . '/../fixtures/firefox-bookmarks.json');

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
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
            ->with($this->callback(function ($persistedEntry) {
                return (bool) $persistedEntry->isArchived();
            }));

        $res = $firefoxImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);

        $this->assertSame(['skipped' => 1, 'imported' => 1, 'queued' => 0], $firefoxImport->getSummary());
    }

    public function testImportWithRabbit()
    {
        $firefoxImport = $this->getFirefoxImport();
        $firefoxImport->setFilepath(__DIR__ . '/../fixtures/firefox-bookmarks.json');

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
            ->expects($this->exactly(1))
            ->method('publish');

        $firefoxImport->setProducer($producer);

        $res = $firefoxImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 1], $firefoxImport->getSummary());
    }

    public function testImportWithRedis()
    {
        $firefoxImport = $this->getFirefoxImport();
        $firefoxImport->setFilepath(__DIR__ . '/../fixtures/firefox-bookmarks.json');

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

        $queue = new RedisQueue($redisMock, 'firefox');
        $producer = new Producer($queue);

        $firefoxImport->setProducer($producer);

        $res = $firefoxImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 1], $firefoxImport->getSummary());

        $this->assertNotEmpty($redisMock->lpop('firefox'));
    }

    public function testImportBadFile()
    {
        $firefoxImport = $this->getFirefoxImport();
        $firefoxImport->setFilepath(__DIR__ . '/../fixtures/wallabag-v1.jsonx');

        $res = $firefoxImport->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('Wallabag Browser Import: unable to read file', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    public function testImportUserNotDefined()
    {
        $firefoxImport = $this->getFirefoxImport(true);
        $firefoxImport->setFilepath(__DIR__ . '/../fixtures/firefox-bookmarks.json');

        $res = $firefoxImport->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('Wallabag Browser Import: user is not defined', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    private function getFirefoxImport($unsetUser = false, $dispatched = 0)
    {
        $this->user = new User();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

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

        $wallabag = new FirefoxImport($this->em, $this->contentProxy, $this->tagsAssigner, $dispatcher);

        $this->logHandler = new TestHandler();
        $logger = new Logger('test', [$this->logHandler]);
        $wallabag->setLogger($logger);

        if (false === $unsetUser) {
            $wallabag->setUser($this->user);
        }

        return $wallabag;
    }
}
