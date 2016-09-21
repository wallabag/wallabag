<?php

namespace Tests\Wallabag\ImportBundle\Import;

use Wallabag\ImportBundle\Import\FirefoxImport;
use Wallabag\UserBundle\Entity\User;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\ImportBundle\Redis\Producer;
use Monolog\Logger;
use Monolog\Handler\TestHandler;
use Simpleue\Queue\RedisQueue;
use M6Web\Component\RedisMock\RedisMockFactory;

class FirefoxImportTest extends \PHPUnit_Framework_TestCase
{
    protected $user;
    protected $em;
    protected $logHandler;
    protected $contentProxy;

    private function getFirefoxImport($unsetUser = false)
    {
        $this->user = new User();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentProxy = $this->getMockBuilder('Wallabag\CoreBundle\Helper\ContentProxy')
            ->disableOriginalConstructor()
            ->getMock();

        $wallabag = new FirefoxImport($this->em, $this->contentProxy);

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
        $firefoxImport = $this->getFirefoxImport();

        $this->assertEquals('Firefox', $firefoxImport->getName());
        $this->assertNotEmpty($firefoxImport->getUrl());
        $this->assertEquals('import.firefox.description', $firefoxImport->getDescription());
    }

    public function testImport()
    {
        $firefoxImport = $this->getFirefoxImport();
        $firefoxImport->setFilepath(__DIR__.'/../fixtures/firefox-bookmarks.json');

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(4))
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
            ->expects($this->exactly(4))
            ->method('updateEntry')
            ->willReturn($entry);

        $res = $firefoxImport->import();

        $this->assertTrue($res);
        $this->assertEquals(['skipped' => 0, 'imported' => 4, 'queued' => 0], $firefoxImport->getSummary());
    }

    public function testImportAndMarkAllAsRead()
    {
        $firefoxImport = $this->getFirefoxImport();
        $firefoxImport->setFilepath(__DIR__.'/../fixtures/firefox-bookmarks.json');

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(4))
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
                return $persistedEntry->isArchived();
            }));

        $res = $firefoxImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);

        $this->assertEquals(['skipped' => 3, 'imported' => 1, 'queued' => 0], $firefoxImport->getSummary());
    }

    public function testImportWithRabbit()
    {
        $firefoxImport = $this->getFirefoxImport();
        $firefoxImport->setFilepath(__DIR__.'/../fixtures/firefox-bookmarks.json');

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
        $this->assertEquals(['skipped' => 0, 'imported' => 0, 'queued' => 1], $firefoxImport->getSummary());
    }

    public function testImportWithRedis()
    {
        $firefoxImport = $this->getFirefoxImport();
        $firefoxImport->setFilepath(__DIR__.'/../fixtures/firefox-bookmarks.json');

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
        $this->assertEquals(['skipped' => 0, 'imported' => 0, 'queued' => 1], $firefoxImport->getSummary());

        $this->assertNotEmpty($redisMock->lpop('firefox'));
    }

    public function testImportBadFile()
    {
        $firefoxImport = $this->getFirefoxImport();
        $firefoxImport->setFilepath(__DIR__.'/../fixtures/wallabag-v1.jsonx');

        $res = $firefoxImport->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertContains('Wallabag Browser Import: unable to read file', $records[0]['message']);
        $this->assertEquals('ERROR', $records[0]['level_name']);
    }

    public function testImportUserNotDefined()
    {
        $firefoxImport = $this->getFirefoxImport(true);
        $firefoxImport->setFilepath(__DIR__.'/../fixtures/firefox-bookmarks.json');

        $res = $firefoxImport->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertContains('Wallabag Browser Import: user is not defined', $records[0]['message']);
        $this->assertEquals('ERROR', $records[0]['level_name']);
    }
}
