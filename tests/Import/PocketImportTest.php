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
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Wallabag\Entity\Config;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Helper\ContentProxy;
use Wallabag\Helper\TagsAssigner;
use Wallabag\Import\PocketImport;
use Wallabag\Redis\Producer;
use Wallabag\Repository\EntryRepository;

class PocketImportTest extends TestCase
{
    protected $token;
    protected $user;
    protected $em;
    protected $contentProxy;
    protected $logHandler;
    protected $tagsAssigner;
    protected $uow;

    public function testInit()
    {
        $pocketImport = $this->getPocketImport();

        $this->assertSame('Pocket', $pocketImport->getName());
        $this->assertNotEmpty($pocketImport->getUrl());
        $this->assertSame('import.pocket.description', $pocketImport->getDescription());
    }

    public function testOAuthRequest()
    {
        $mockHttpClient = new MockHttpClient([new MockResponse(json_encode(['code' => 'wunderbar_code']), ['response_headers' => ['Content-Type: application/json']])]);

        $pocketImport = $this->getPocketImport();
        $pocketImport->setClient($mockHttpClient);

        $code = $pocketImport->getRequestToken('http://0.0.0.0/redirect');

        $this->assertSame('wunderbar_code', $code);
    }

    public function testOAuthRequestBadResponse()
    {
        $mockHttpClient = new MockHttpClient([new MockResponse('', ['http_code' => 403])]);

        $pocketImport = $this->getPocketImport();
        $pocketImport->setClient($mockHttpClient);

        $code = $pocketImport->getRequestToken('http://0.0.0.0/redirect');

        $this->assertFalse($code);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('PocketImport: Failed to request token', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    public function testOAuthAuthorize()
    {
        $mockHttpClient = new MockHttpClient([new MockResponse(json_encode(['access_token' => 'wunderbar_token']), ['response_headers' => ['Content-Type: application/json']])]);

        $pocketImport = $this->getPocketImport();
        $pocketImport->setClient($mockHttpClient);

        $res = $pocketImport->authorize('wunderbar_code');

        $this->assertTrue($res);
        $this->assertSame('wunderbar_token', $pocketImport->getAccessToken());
    }

    public function testOAuthAuthorizeBadResponse()
    {
        $mockHttpClient = new MockHttpClient([new MockResponse('', ['http_code' => 403])]);

        $pocketImport = $this->getPocketImport();
        $pocketImport->setClient($mockHttpClient);

        $res = $pocketImport->authorize('wunderbar_code');

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('PocketImport: Failed to authorize client', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    /**
     * Will sample results from https://getpocket.com/developer/docs/v3/retrieve.
     */
    public function testImport()
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse(json_encode(['access_token' => 'wunderbar_token']), ['response_headers' => ['Content-Type: application/json']]),
            new MockResponse(<<<'JSON'
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
                        "time_added": "1473020899",
                        "time_updated": "1473020899",
                        "time_read": "0",
                        "time_favorited": "0",
                        "sort_id": 0,
                        "resolved_title": "The Massive Ryder Cup Preview",
                        "resolved_url": "http://www.grantland.com/blog/the-triangle/post/_/id/38347/ryder-cup-preview",
                        "excerpt": "The list of things I love about the Ryder Cup is so long that it could fill a (tedious) novel, and golf fans can probably guess most of them.",
                        "is_article": "1",
                        "is_index": "0",
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
                        "time_added": "1473020899",
                        "time_updated": "1473020899",
                        "time_read": "0",
                        "time_favorited": "0",
                        "sort_id": 1,
                        "resolved_title": "The Massive Ryder Cup Preview",
                        "resolved_url": "http://www.grantland.com/blog/the-triangle/post/_/id/38347/ryder-cup-preview",
                        "excerpt": "The list of things I love about the Ryder Cup is so long that it could fill a (tedious) novel, and golf fans can probably guess most of them.",
                        "is_article": "1",
                        "is_index": "0",
                        "has_video": "0",
                        "has_image": "0",
                        "word_count": "3197"
                    }
                }
            }
JSON
                , ['response_headers' => ['Content-Type: application/json']]),
        ]);

        $pocketImport = $this->getPocketImport('ConsumerKey', 1);

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->exactly(2))
            ->method('findByUrlAndUserId')
            ->will($this->onConsecutiveCalls(false, true));

        $this->em
            ->expects($this->exactly(2))
            ->method('getRepository')
            ->willReturn($entryRepo);

        $this->em
            ->expects($this->any())
            ->method('persist')
            ->with($this->callback(fn ($persistedEntry) => (bool) $persistedEntry->isArchived() && (bool) $persistedEntry->isStarred()));

        $entry = new Entry($this->user);

        $this->contentProxy
            ->expects($this->once())
            ->method('updateEntry')
            ->willReturn($entry);

        $pocketImport->setClient($mockHttpClient);
        $pocketImport->authorize('wunderbar_code');

        $res = $pocketImport->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 1, 'imported' => 1, 'queued' => 0], $pocketImport->getSummary());
    }

    /**
     * Will sample results from https://getpocket.com/developer/docs/v3/retrieve.
     */
    public function testImportAndMarkAllAsRead()
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse(json_encode(['access_token' => 'wunderbar_token']), ['response_headers' => ['Content-Type: application/json']]),
            new MockResponse(<<<'JSON'
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
                        "time_added": "1473020899",
                        "time_updated": "1473020899",
                        "time_read": "0",
                        "time_favorited": "0",
                        "sort_id": 0,
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
                        "time_added": "1473020899",
                        "time_updated": "1473020899",
                        "time_read": "0",
                        "time_favorited": "0",
                        "sort_id": 1,
                        "excerpt": "The list of things I love about the Ryder Cup is so long that it could fill a (tedious) novel, and golf fans can probably guess most of them.",
                        "is_article": "1",
                        "has_video": "0",
                        "has_image": "0",
                        "word_count": "3197"
                    }
                }
            }
JSON
                , ['response_headers' => ['Content-Type: application/json']]),
        ]);

        $pocketImport = $this->getPocketImport('ConsumerKey', 2);

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
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
            ->with($this->callback(fn ($persistedEntry) => (bool) $persistedEntry->isArchived()));

        $entry = new Entry($this->user);

        $this->contentProxy
            ->expects($this->exactly(2))
            ->method('updateEntry')
            ->willReturn($entry);

        $pocketImport->setClient($mockHttpClient);
        $pocketImport->authorize('wunderbar_code');

        $res = $pocketImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 2, 'queued' => 0], $pocketImport->getSummary());
    }

    /**
     * Will sample results from https://getpocket.com/developer/docs/v3/retrieve.
     */
    public function testImportWithRabbit()
    {
        $body = <<<'JSON'
{
    "item_id": "229279689",
    "resolved_id": "229279689",
    "given_url": "http://www.grantland.com/blog/the-triangle/post/_/id/38347/ryder-cup-preview",
    "given_title": "The Massive Ryder Cup Preview - The Triangle Blog - Grantland",
    "favorite": "1",
    "status": "1",
    "time_added": "1473020899",
    "time_updated": "1473020899",
    "time_read": "0",
    "time_favorited": "0",
    "sort_id": 0,
    "resolved_title": "The Massive Ryder Cup Preview",
    "resolved_url": "http://www.grantland.com/blog/the-triangle/post/_/id/38347/ryder-cup-preview",
    "excerpt": "The list of things I love about the Ryder Cup is so long that it could fill a (tedious) novel, and golf fans can probably guess most of them.",
    "is_article": "1",
    "has_video": "0",
    "has_image": "0",
    "word_count": "3197"
}
JSON;

        $mockHttpClient = new MockHttpClient([
            new MockResponse(json_encode(['access_token' => 'wunderbar_token']), ['response_headers' => ['Content-Type: application/json']]),
            new MockResponse(<<<JSON
            {
                "status": 1,
                "list": {
                    "229279690": $body
                }
            }
JSON
                , ['response_headers' => ['Content-Type: application/json']]),
        ]);

        $pocketImport = $this->getPocketImport();

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->never())
            ->method('findByUrlAndUserId');

        $this->em
            ->expects($this->never())
            ->method('getRepository');

        $entry = new Entry($this->user);

        $this->contentProxy
            ->expects($this->never())
            ->method('updateEntry');

        $producer = $this->getMockBuilder(\OldSound\RabbitMqBundle\RabbitMq\Producer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $bodyAsArray = json_decode($body, true);
        // because with just use `new User()` so it doesn't have an id
        $bodyAsArray['userId'] = null;

        $producer
            ->expects($this->once())
            ->method('publish')
            ->with(json_encode($bodyAsArray));

        $pocketImport->setClient($mockHttpClient);
        $pocketImport->setProducer($producer);
        $pocketImport->authorize('wunderbar_code');

        $res = $pocketImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 1], $pocketImport->getSummary());
    }

    /**
     * Will sample results from https://getpocket.com/developer/docs/v3/retrieve.
     */
    public function testImportWithRedis()
    {
        $body = <<<'JSON'
{
    "item_id": "229279689",
    "resolved_id": "229279689",
    "given_url": "http://www.grantland.com/blog/the-triangle/post/_/id/38347/ryder-cup-preview",
    "given_title": "The Massive Ryder Cup Preview - The Triangle Blog - Grantland",
    "favorite": "1",
    "status": "1",
    "time_added": "1473020899",
    "time_updated": "1473020899",
    "time_read": "0",
    "time_favorited": "0",
    "sort_id": 0,
    "resolved_title": "The Massive Ryder Cup Preview",
    "resolved_url": "http://www.grantland.com/blog/the-triangle/post/_/id/38347/ryder-cup-preview",
    "excerpt": "The list of things I love about the Ryder Cup is so long that it could fill a (tedious) novel, and golf fans can probably guess most of them.",
    "is_article": "1",
    "has_video": "0",
    "has_image": "0",
    "word_count": "3197"
}
JSON;

        $mockHttpClient = new MockHttpClient([
            new MockResponse(json_encode(['access_token' => 'wunderbar_token']), ['response_headers' => ['Content-Type: application/json']]),
            new MockResponse(<<<JSON
            {
                "status": 1,
                "list": {
                    "229279690": $body
                }
            }
JSON
                , ['response_headers' => ['Content-Type: application/json']]),
        ]);

        $pocketImport = $this->getPocketImport();

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->never())
            ->method('findByUrlAndUserId');

        $this->em
            ->expects($this->never())
            ->method('getRepository');

        $entry = new Entry($this->user);

        $this->contentProxy
            ->expects($this->never())
            ->method('updateEntry');

        $factory = new RedisMockFactory();
        $redisMock = $factory->getAdapter(Client::class, true);

        $queue = new RedisQueue($redisMock, 'pocket');
        $producer = new Producer($queue);

        $pocketImport->setClient($mockHttpClient);
        $pocketImport->setProducer($producer);
        $pocketImport->authorize('wunderbar_code');

        $res = $pocketImport->setMarkAsRead(true)->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 0, 'queued' => 1], $pocketImport->getSummary());

        $this->assertNotEmpty($redisMock->lpop('pocket'));
    }

    public function testImportBadResponse()
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse(json_encode(['access_token' => 'wunderbar_token']), ['response_headers' => ['Content-Type: application/json']]),
            new MockResponse('', ['http_code' => 403]),
        ]);

        $pocketImport = $this->getPocketImport();
        $pocketImport->setClient($mockHttpClient);
        $pocketImport->authorize('wunderbar_code');

        $res = $pocketImport->import();

        $this->assertFalse($res);

        $records = $this->logHandler->getRecords();
        $this->assertStringContainsString('PocketImport: Failed to import', $records[0]['message']);
        $this->assertSame('ERROR', $records[0]['level_name']);
    }

    public function testImportWithExceptionFromGraby()
    {
        $mockHttpClient = new MockHttpClient([
            new MockResponse(json_encode(['access_token' => 'wunderbar_token']), ['response_headers' => ['Content-Type: application/json']]),
            new MockResponse(<<<'JSON'
            {
                "status": 1,
                "list": {
                    "229279689": {
                        "status": "1",
                        "favorite": "1",
                        "resolved_url": "http://www.grantland.com/blog/the-triangle/post/_/id/38347/ryder-cup-preview"
                    }
                }
            }

JSON
                , ['response_headers' => ['Content-Type: application/json']]),
        ]);

        $pocketImport = $this->getPocketImport('ConsumerKey', 1);

        $entryRepo = $this->getMockBuilder(EntryRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entryRepo->expects($this->once())
            ->method('findByUrlAndUserId')
            ->will($this->onConsecutiveCalls(false, true));

        $this->em
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($entryRepo);

        $entry = new Entry($this->user);

        $this->contentProxy
            ->expects($this->once())
            ->method('updateEntry')
            ->will($this->throwException(new \Exception()));

        $pocketImport->setClient($mockHttpClient);
        $pocketImport->authorize('wunderbar_code');

        $res = $pocketImport->import();

        $this->assertTrue($res);
        $this->assertSame(['skipped' => 0, 'imported' => 1, 'queued' => 0], $pocketImport->getSummary());
    }

    private function getPocketImport($consumerKey = 'ConsumerKey', $dispatched = 0)
    {
        $this->user = new User();

        $config = new Config($this->user);
        $config->setPocketConsumerKey('xxx');

        $this->user->setConfig($config);

        $this->contentProxy = $this->getMockBuilder(ContentProxy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tagsAssigner = $this->getMockBuilder(TagsAssigner::class)
            ->disableOriginalConstructor()
            ->getMock();

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

        $dispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dispatcher
            ->expects($this->exactly($dispatched))
            ->method('dispatch');

        $this->logHandler = new TestHandler();
        $logger = new Logger('test', [$this->logHandler]);

        $pocket = new PocketImport($this->em, $this->contentProxy, $this->tagsAssigner, $dispatcher, $logger);
        $pocket->setUser($this->user);

        return $pocket;
    }
}
