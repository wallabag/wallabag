<?php

namespace Wallabag\ImportBundle\Tests\Import;

use Wallabag\UserBundle\Entity\User;
use Wallabag\ImportBundle\Import\WallabagV1Import;
use Monolog\Logger;
use Monolog\Handler\TestHandler;

class WallabagV1ImportTest extends \PHPUnit_Framework_TestCase
{
    protected $user;
    protected $em;
    protected $logHandler;
    protected $contentProxy;

    private function getWallabagV1Import($unsetUser = false)
    {
        $this->user = new User();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentProxy = $this->getMockBuilder('Wallabag\CoreBundle\Helper\ContentProxy')
            ->disableOriginalConstructor()
            ->getMock();

        $wallabag = new WallabagV1Import($this->em, $this->contentProxy);

        $this->logHandler = new TestHandler();
        $logger = new Logger('test', array($this->logHandler));
        $wallabag->setLogger($logger);

        if (false === $unsetUser) {
            $wallabag->setUser($this->user);
        }

        return $wallabag;
    }

    public function testInit()
    {
        $wallabagV1Import = $this->getWallabagV1Import();

        $this->assertEquals('wallabag v1', $wallabagV1Import->getName());
        $this->assertNotEmpty($wallabagV1Import->getUrl());
        $this->assertEquals('import.wallabag_v1.description', $wallabagV1Import->getDescription());
    }

    public function testImport()
    {
        $wallabagV1Import = $this->getWallabagV1Import();
        $wallabagV1Import->setFilepath(__DIR__.'/../fixtures/wallabag-v1.json');

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(4))
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
            ->expects($this->once())
            ->method('updateEntry')
            ->willReturn($entry);

        $res = $wallabagV1Import->import();

        $this->assertTrue($res);
        $this->assertEquals(['skipped' => 1, 'imported' => 3], $wallabagV1Import->getSummary());
    }

    public function testImportAndMarkAllAsRead()
    {
        $wallabagV1Import = $this->getWallabagV1Import();
        $wallabagV1Import->setFilepath(__DIR__.'/../fixtures/wallabag-v1-read.json');

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

        // check that every entry persisted are archived
        $this->em
            ->expects($this->any())
            ->method('persist')
            ->with($this->callback(function ($persistedEntry) {
                return $persistedEntry->isArchived();
            }));

        $res = $wallabagV1Import->setMarkAsRead(true)->import();

        $this->assertTrue($res);

        $this->assertEquals(['skipped' => 0, 'imported' => 3], $wallabagV1Import->getSummary());
    }

    public function testImportBadFile()
    {
        $wallabagV1Import = $this->getWallabagV1Import();
        $wallabagV1Import->setFilepath(__DIR__.'/../fixtures/wallabag-v1.jsonx');

        $res = $wallabagV1Import->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertContains('WallabagImport: unable to read file', $records[0]['message']);
        $this->assertEquals('ERROR', $records[0]['level_name']);
    }

    public function testImportUserNotDefined()
    {
        $wallabagV1Import = $this->getWallabagV1Import(true);
        $wallabagV1Import->setFilepath(__DIR__.'/../fixtures/wallabag-v1.json');

        $res = $wallabagV1Import->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertContains('WallabagImport: user is not defined', $records[0]['message']);
        $this->assertEquals('ERROR', $records[0]['level_name']);
    }
}
