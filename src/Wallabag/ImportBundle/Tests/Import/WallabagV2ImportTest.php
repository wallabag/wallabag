<?php

namespace Wallabag\ImportBundle\Tests\Import;

use Wallabag\ImportBundle\Import\WallabagV2Import;
use Wallabag\UserBundle\Entity\User;
use Monolog\Logger;
use Monolog\Handler\TestHandler;

class WallabagV2ImportTest extends \PHPUnit_Framework_TestCase
{
    protected $user;
    protected $em;
    protected $logHandler;
    protected $contentProxy;

    private function getWallabagV2Import($unsetUser = false)
    {
        $this->user = new User();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentProxy = $this->getMockBuilder('Wallabag\CoreBundle\Helper\ContentProxy')
            ->disableOriginalConstructor()
            ->getMock();

        $wallabag = new WallabagV2Import($this->em, $this->contentProxy);

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
        $wallabagV2Import = $this->getWallabagV2Import();

        $this->assertEquals('wallabag v2', $wallabagV2Import->getName());
        $this->assertNotEmpty($wallabagV2Import->getUrl());
        $this->assertContains('This importer will import all your wallabag v2 articles.', $wallabagV2Import->getDescription());
    }

    public function testImport()
    {
        $wallabagV2Import = $this->getWallabagV2Import();
        $wallabagV2Import->setFilepath(__DIR__.'/../fixtures/wallabag-v2.json');

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(2))
            ->method('findByUrlAndUserId')
            ->will($this->onConsecutiveCalls(false, true, false));

        $this->em
            ->expects($this->any())
            ->method('getRepository')
            ->willReturn($entryRepo);

        $res = $wallabagV2Import->import();

        $this->assertTrue($res);
        $this->assertEquals(['skipped' => 1, 'imported' => 1], $wallabagV2Import->getSummary());
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
}
