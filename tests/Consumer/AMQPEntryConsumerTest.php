<?php

namespace Tests\Wallabag\Consumer;

use Doctrine\ORM\EntityManager;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Wallabag\Consumer\AMQPEntryConsumer;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Import\AbstractImport;
use Wallabag\Repository\UserRepository;

class AMQPEntryConsumerTest extends TestCase
{
    public function testMessageOk()
    {
        $em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $em
            ->expects($this->once())
            ->method('flush');

        $em
            ->expects($this->once())
            ->method('clear');

        $body = <<<'JSON'
{
    "item_id": "1402935436",
    "resolved_id": "1402935436",
    "given_url": "http://mashable.com/2016/09/04/leslie-jones-back-on-twitter-after-hack/?utm_campaign=Mash-Prod-RSS-Feedburner-All-Partial&utm_cid=Mash-Prod-RSS-Feedburner-All-Partial",
    "given_title": "Leslie Jones is back on Twitter and her comeback tweet rules",
    "favorite": "0",
    "status": "0",
    "time_added": "1473020899",
    "time_updated": "1473020899",
    "time_read": "0",
    "time_favorited": "0",
    "sort_id": 0,
    "resolved_title": "Leslie Jones is back on Twitter and her comeback tweet rules",
    "resolved_url": "http://mashable.com/2016/09/04/leslie-jones-back-on-twitter-after-hack/?utm_campaign=Mash-Prod-RSS-Feedburner-All-Partial&utm_cid=Mash-Prod-RSS-Feedburner-All-Partial",
    "excerpt": "Leslie Jones is back to communicating with her adoring public on Twitter after cowardly hacker-trolls drove her away, probably to compensate for their own failings.  It all started with a mic drop ...",
    "is_article": "1",
    "is_index": "0",
    "has_video": "0",
    "has_image": "1",
    "word_count": "200",
    "tags": {
        "ifttt": {
            "item_id": "1402935436",
            "tag": "ifttt"
        },
        "mashable": {
            "item_id": "1402935436",
            "tag": "mashable"
        }
    },
    "authors": {
        "2484273": {
            "item_id": "1402935436",
            "author_id": "2484273",
            "name": "Adam Rosenberg",
            "url": "http://mashable.com/author/adam-rosenberg/"
        }
    },
    "image": {
        "item_id": "1402935436",
        "src": "http://i.amz.mshcdn.com/i-V5cS6_sDqFABaVR0hVSBJqG_w=/950x534/https%3A%2F%2Fblueprint-api-production.s3.amazonaws.com%2Fuploads%2Fcard%2Fimage%2F199899%2Fleslie_jones_war_dogs.jpg",
        "width": "0",
        "height": "0"
    },
    "images": {
        "1": {
            "item_id": "1402935436",
            "image_id": "1",
            "src": "http://i.amz.mshcdn.com/i-V5cS6_sDqFABaVR0hVSBJqG_w=/950x534/https%3A%2F%2Fblueprint-api-production.s3.amazonaws.com%2Fuploads%2Fcard%2Fimage%2F199899%2Fleslie_jones_war_dogs.jpg",
            "width": "0",
            "height": "0",
            "credit": "Image:  Steve Eichner/NameFace/Sipa USA",
            "caption": ""
        }
    },
    "userId": 1
}
JSON;

        $user = new User();
        $entry = new Entry($user);

        $userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userRepository
            ->expects($this->once())
            ->method('find')
            // userId from the body json above
            ->with(1)
            ->willReturn($user);

        $import = $this->getMockBuilder(AbstractImport::class)
            ->disableOriginalConstructor()
            ->getMock();

        $import
            ->expects($this->once())
            ->method('setUser')
            ->with($user);

        $import
            ->expects($this->once())
            ->method('parseEntry')
            ->with(json_decode($body, true))
            ->willReturn($entry);

        $dispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dispatcher
            ->expects($this->once())
            ->method('dispatch');

        $consumer = new AMQPEntryConsumer(
            $em,
            $userRepository,
            $import,
            $dispatcher
        );

        $message = new AMQPMessage($body);

        $consumer->execute($message);
    }

    public function testMessageWithBadUser()
    {
        $em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $em
            ->expects($this->never())
            ->method('flush');

        $em
            ->expects($this->never())
            ->method('clear');

        $body = '{ "userId": 123 }';

        $user = new User();
        $entry = new Entry($user);

        $userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userRepository
            ->expects($this->once())
            ->method('find')
            // userId from the body json above
            ->with(123)
            ->willReturn(null);

        $import = $this->getMockBuilder(AbstractImport::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dispatcher
            ->expects($this->never())
            ->method('dispatch');

        $consumer = new AMQPEntryConsumer(
            $em,
            $userRepository,
            $import,
            $dispatcher
        );

        $message = new AMQPMessage($body);

        $res = $consumer->execute($message);

        $this->assertTrue($res);
    }

    public function testMessageWithEntryProcessed()
    {
        $em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $em
            ->expects($this->never())
            ->method('flush');

        $em
            ->expects($this->never())
            ->method('clear');

        $body = '{ "userId": 123 }';

        $user = new User();

        $userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userRepository
            ->expects($this->once())
            ->method('find')
            // userId from the body json above
            ->with(123)
            ->willReturn($user);

        $import = $this->getMockBuilder(AbstractImport::class)
            ->disableOriginalConstructor()
            ->getMock();

        $import
            ->expects($this->once())
            ->method('setUser')
            ->with($user);

        $import
            ->expects($this->once())
            ->method('parseEntry')
            ->with(json_decode($body, true))
            ->willReturn(null);

        $dispatcher = $this->getMockBuilder(EventDispatcher::class)
            ->disableOriginalConstructor()
            ->getMock();

        $dispatcher
            ->expects($this->never())
            ->method('dispatch');

        $consumer = new AMQPEntryConsumer(
            $em,
            $userRepository,
            $import,
            $dispatcher
        );

        $message = new AMQPMessage($body);

        $consumer->execute($message);
    }
}
