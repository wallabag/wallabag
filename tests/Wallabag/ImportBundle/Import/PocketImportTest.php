<?php

namespace Tests\Wallabag\ImportBundle\Import;

use Wallabag\UserBundle\Entity\User;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\ImportBundle\Import\PocketImport;
use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use Monolog\Logger;
use Monolog\Handler\TestHandler;

class PocketImportMock extends PocketImport
{
    public function getAccessToken()
    {
        return $this->accessToken;
    }
}

class PocketImportTest extends \PHPUnit_Framework_TestCase
{
    protected $token;
    protected $user;
    protected $em;
    protected $contentProxy;
    protected $logHandler;

    private function getPocketImport($consumerKey = 'ConsumerKey')
    {
        $this->user = new User();

        $this->tokenStorage = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->contentProxy = $this->getMockBuilder('Wallabag\CoreBundle\Helper\ContentProxy')
            ->disableOriginalConstructor()
            ->getMock();

        $token->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->tokenStorage->expects($this->once())
            ->method('getToken')
            ->willReturn($token);

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $config = $this->getMockBuilder('Craue\ConfigBundle\Util\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $config->expects($this->any())
            ->method('get')
            ->with('pocket_consumer_key')
            ->willReturn($consumerKey);

        $pocket = new PocketImportMock(
            $this->tokenStorage,
            $this->em,
            $this->contentProxy,
            $config
        );

        $this->logHandler = new TestHandler();
        $logger = new Logger('test', [$this->logHandler]);
        $pocket->setLogger($logger);

        return $pocket;
    }

    public function testInit()
    {
        $pocketImport = $this->getPocketImport();

        $this->assertEquals('Pocket', $pocketImport->getName());
        $this->assertNotEmpty($pocketImport->getUrl());
        $this->assertEquals('import.pocket.description', $pocketImport->getDescription());
    }

    public function testOAuthRequest()
    {
        $client = new Client();

        $mock = new Mock([
            new Response(200, ['Content-Type' => 'application/json'], Stream::factory(json_encode(['code' => 'wunderbar_code']))),
        ]);

        $client->getEmitter()->attach($mock);

        $pocketImport = $this->getPocketImport();
        $pocketImport->setClient($client);

        $code = $pocketImport->getRequestToken('http://0.0.0.0/redirect');

        $this->assertEquals('wunderbar_code', $code);
    }

    public function testOAuthRequestBadResponse()
    {
        $client = new Client();

        $mock = new Mock([
            new Response(403),
        ]);

        $client->getEmitter()->attach($mock);

        $pocketImport = $this->getPocketImport();
        $pocketImport->setClient($client);

        $code = $pocketImport->getRequestToken('http://0.0.0.0/redirect');

        $this->assertFalse($code);

        $records = $this->logHandler->getRecords();
        $this->assertContains('PocketImport: Failed to request token', $records[0]['message']);
        $this->assertEquals('ERROR', $records[0]['level_name']);
    }

    public function testOAuthAuthorize()
    {
        $client = new Client();

        $mock = new Mock([
            new Response(200, ['Content-Type' => 'application/json'], Stream::factory(json_encode(['access_token' => 'wunderbar_token']))),
        ]);

        $client->getEmitter()->attach($mock);

        $pocketImport = $this->getPocketImport();
        $pocketImport->setClient($client);

        $res = $pocketImport->authorize('wunderbar_code');

        $this->assertTrue($res);
        $this->assertEquals('wunderbar_token', $pocketImport->getAccessToken());
    }

    public function testOAuthAuthorizeBadResponse()
    {
        $client = new Client();

        $mock = new Mock([
            new Response(403),
        ]);

        $client->getEmitter()->attach($mock);

        $pocketImport = $this->getPocketImport();
        $pocketImport->setClient($client);

        $res = $pocketImport->authorize('wunderbar_code');

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertContains('PocketImport: Failed to authorize client', $records[0]['message']);
        $this->assertEquals('ERROR', $records[0]['level_name']);
    }

    /**
     * Will sample results from https://getpocket.com/developer/docs/v3/retrieve.
     */
    public function testImport()
    {
        $client = new Client();

        $mock = new Mock([
            new Response(200, ['Content-Type' => 'application/json'], Stream::factory(json_encode(['access_token' => 'wunderbar_token']))),
            new Response(200, ['Content-Type' => 'application/json'], Stream::factory('
                {
                    "status": 1,
                    "list": {
                        "229279689": {
                            "item_id": "229279689",
                            "resolved_id": "229279689",
                            "given_url": "http://www.grantland.com/blog/the-triangle/post/_/id/38347/ryder-cup-preview",
                            "given_title": "The Massive Ryder Cup Preview - The Triangle Blog - Grantland",
                            "favorite": "1",
                            "status": "1",
                            "resolved_title": "The Massive Ryder Cup Preview",
                            "resolved_url": "http://www.grantland.com/blog/the-triangle/post/_/id/38347/ryder-cup-preview",
                            "excerpt": "The list of things I love about the Ryder Cup is so long that it could fill a (tedious) novel, and golf fans can probably guess most of them.",
                            "is_article": "1",
                            "has_video": "1",
                            "has_image": "1",
                            "word_count": "3197",
                            "images": {
                                "1": {
                                    "item_id": "229279689",
                                    "image_id": "1",
                                    "src": "http://a.espncdn.com/combiner/i?img=/photo/2012/0927/grant_g_ryder_cr_640.jpg&w=640&h=360",
                                    "width": "0",
                                    "height": "0",
                                    "credit": "Jamie Squire/Getty Images",
                                    "caption": ""
                                }
                            },
                            "videos": {
                                "1": {
                                    "item_id": "229279689",
                                    "video_id": "1",
                                    "src": "http://www.youtube.com/v/Er34PbFkVGk?version=3&hl=en_US&rel=0",
                                    "width": "420",
                                    "height": "315",
                                    "type": "1",
                                    "vid": "Er34PbFkVGk"
                                }
                            },
                            "tags": {
                                "grantland": {
                                    "item_id": "1147652870",
                                    "tag": "grantland"
                                },
                                "Ryder Cup": {
                                    "item_id": "1147652870",
                                    "tag": "Ryder Cup"
                                }
                            }
                        },
                        "229279690": {
                            "item_id": "229279689",
                            "resolved_id": "229279689",
                            "given_url": "http://www.grantland.com/blog/the-triangle/post/_/id/38347/ryder-cup-preview",
                            "given_title": "The Massive Ryder Cup Preview - The Triangle Blog - Grantland",
                            "favorite": "1",
                            "status": "1",
                            "resolved_title": "The Massive Ryder Cup Preview",
                            "resolved_url": "http://www.grantland.com/blog/the-triangle/post/_/id/38347/ryder-cup-preview",
                            "excerpt": "The list of things I love about the Ryder Cup is so long that it could fill a (tedious) novel, and golf fans can probably guess most of them.",
                            "is_article": "1",
                            "has_video": "0",
                            "has_image": "0",
                            "word_count": "3197"
                        }
                    }
                }
            ')),
        ]);

        $client->getEmitter()->attach($mock);

        $pocketImport = $this->getPocketImport();

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(2))
            ->method('findByUrlAndUserId')
            ->will($this->onConsecutiveCalls(false, true));

        $this->em
            ->expects($this->exactly(2))
            ->method('getRepository')
            ->willReturn($entryRepo);

        $entry = new Entry($this->user);

        $this->contentProxy
            ->expects($this->once())
            ->method('updateEntry')
            ->willReturn($entry);

        $pocketImport->setClient($client);
        $pocketImport->authorize('wunderbar_code');

        $res = $pocketImport->import();

        $this->assertTrue($res);
        $this->assertEquals(['skipped' => 1, 'imported' => 1], $pocketImport->getSummary());
    }

    /**
     * Will sample results from https://getpocket.com/developer/docs/v3/retrieve.
     */
    public function testImportAndMarkAllAsRead()
    {
        $client = new Client();

        $mock = new Mock([
            new Response(200, ['Content-Type' => 'application/json'], Stream::factory(json_encode(['access_token' => 'wunderbar_token']))),
            new Response(200, ['Content-Type' => 'application/json'], Stream::factory('
                {
                    "status": 1,
                    "list": {
                        "229279689": {
                            "item_id": "229279689",
                            "resolved_id": "229279689",
                            "given_url": "http://www.grantland.com/blog/the-triangle/post/_/id/38347/ryder-cup-preview",
                            "given_title": "The Massive Ryder Cup Preview - The Triangle Blog - Grantland",
                            "favorite": "1",
                            "status": "1",
                            "excerpt": "The list of things I love about the Ryder Cup is so long that it could fill a (tedious) novel, and golf fans can probably guess most of them.",
                            "is_article": "1",
                            "has_video": "1",
                            "has_image": "1",
                            "word_count": "3197"
                        },
                        "229279690": {
                            "item_id": "229279689",
                            "resolved_id": "229279689",
                            "given_url": "http://www.grantland.com/blog/the-triangle/post/_/id/38347/ryder-cup-preview/2",
                            "given_title": "The Massive Ryder Cup Preview - The Triangle Blog - Grantland",
                            "favorite": "1",
                            "status": "0",
                            "excerpt": "The list of things I love about the Ryder Cup is so long that it could fill a (tedious) novel, and golf fans can probably guess most of them.",
                            "is_article": "1",
                            "has_video": "0",
                            "has_image": "0",
                            "word_count": "3197"
                        }
                    }
                }
            ')),
        ]);

        $client->getEmitter()->attach($mock);

        $pocketImport = $this->getPocketImport();

        $entryRepo = $this->getMockBuilder('Wallabag\CoreBundle\Repository\EntryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(2))
            ->method('findByUrlAndUserId')
            ->will($this->onConsecutiveCalls(false, false));

        $this->em
            ->expects($this->exactly(2))
            ->method('getRepository')
            ->willReturn($entryRepo);

        // check that every entry persisted are archived
        $this->em
            ->expects($this->any())
            ->method('persist')
            ->with($this->callback(function ($persistedEntry) {
                return $persistedEntry->isArchived();
            }));

        $entry = new Entry($this->user);

        $this->contentProxy
            ->expects($this->exactly(2))
            ->method('updateEntry')
            ->willReturn($entry);

        $pocketImport->setClient($client);
        $pocketImport->authorize('wunderbar_code');

        $res = $pocketImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertEquals(['skipped' => 0, 'imported' => 2], $pocketImport->getSummary());
    }

    public function testImportBadResponse()
    {
        $client = new Client();

        $mock = new Mock([
            new Response(200, ['Content-Type' => 'application/json'], Stream::factory(json_encode(['access_token' => 'wunderbar_token']))),
            new Response(403),
        ]);

        $client->getEmitter()->attach($mock);

        $pocketImport = $this->getPocketImport();
        $pocketImport->setClient($client);
        $pocketImport->authorize('wunderbar_code');

        $res = $pocketImport->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertContains('PocketImport: Failed to import', $records[0]['message']);
        $this->assertEquals('ERROR', $records[0]['level_name']);
    }
}
