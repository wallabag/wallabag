<?php

namespace Tests\Wallabag\CoreBundle\Helper;

use Psr\Log\NullLogger;
use Wallabag\CoreBundle\Helper\ContentProxy;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\CoreBundle\Entity\Tag;
use Wallabag\UserBundle\Entity\User;
use Wallabag\CoreBundle\Helper\RuleBasedTagger;
use Graby\Graby;
use Monolog\Handler\TestHandler;
use Monolog\Logger;

class ContentProxyTest extends \PHPUnit_Framework_TestCase
{
    private $fetchingErrorMessage = 'wallabag can\'t retrieve contents for this article. Please <a href="http://doc.wallabag.org/en/user/errors_during_fetching.html#how-can-i-help-to-fix-that">troubleshoot this issue</a>.';

    public function testWithBadUrl()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $graby = $this->getMockBuilder('Graby\Graby')
            ->setMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => false,
                'title' => '',
                'url' => '',
                'content_type' => '',
                'language' => '',
            ]);

        $proxy = new ContentProxy($graby, $tagger, $this->getLogger(), $this->fetchingErrorMessage);
        $entry = $proxy->updateEntry(new Entry(new User()), 'http://user@:80');

        $this->assertEquals('http://user@:80', $entry->getUrl());
        $this->assertEmpty($entry->getTitle());
        $this->assertEquals($this->fetchingErrorMessage, $entry->getContent());
        $this->assertEmpty($entry->getPreviewPicture());
        $this->assertEmpty($entry->getMimetype());
        $this->assertEmpty($entry->getLanguage());
        $this->assertEquals(0.0, $entry->getReadingTime());
        $this->assertEquals(false, $entry->getDomainName());
    }

    public function testWithEmptyContent()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $graby = $this->getMockBuilder('Graby\Graby')
            ->setMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => false,
                'title' => '',
                'url' => '',
                'content_type' => '',
                'language' => '',
            ]);

        $proxy = new ContentProxy($graby, $tagger, $this->getLogger(), $this->fetchingErrorMessage);
        $entry = $proxy->updateEntry(new Entry(new User()), 'http://0.0.0.0');

        $this->assertEquals('http://0.0.0.0', $entry->getUrl());
        $this->assertEmpty($entry->getTitle());
        $this->assertEquals($this->fetchingErrorMessage, $entry->getContent());
        $this->assertEmpty($entry->getPreviewPicture());
        $this->assertEmpty($entry->getMimetype());
        $this->assertEmpty($entry->getLanguage());
        $this->assertEquals(0.0, $entry->getReadingTime());
        $this->assertEquals('0.0.0.0', $entry->getDomainName());
    }

    public function testWithEmptyContentButOG()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $graby = $this->getMockBuilder('Graby\Graby')
            ->setMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => false,
                'title' => '',
                'url' => '',
                'content_type' => '',
                'language' => '',
                'status' => '',
                'open_graph' => [
                    'og_title' => 'my title',
                    'og_description' => 'desc',
                ],
            ]);

        $proxy = new ContentProxy($graby, $tagger, $this->getLogger(), $this->fetchingErrorMessage);
        $entry = $proxy->updateEntry(new Entry(new User()), 'http://domain.io');

        $this->assertEquals('http://domain.io', $entry->getUrl());
        $this->assertEquals('my title', $entry->getTitle());
        $this->assertEquals($this->fetchingErrorMessage.'<p><i>But we found a short description: </i></p>desc', $entry->getContent());
        $this->assertEmpty($entry->getPreviewPicture());
        $this->assertEmpty($entry->getLanguage());
        $this->assertEmpty($entry->getHttpStatus());
        $this->assertEmpty($entry->getMimetype());
        $this->assertEquals(0.0, $entry->getReadingTime());
        $this->assertEquals('domain.io', $entry->getDomainName());
    }

    public function testWithContent()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $graby = $this->getMockBuilder('Graby\Graby')
            ->setMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => str_repeat('this is my content', 325),
                'title' => 'this is my title',
                'url' => 'http://1.1.1.1',
                'content_type' => 'text/html',
                'language' => 'fr',
                'status' => '200',
                'open_graph' => [
                    'og_title' => 'my OG title',
                    'og_description' => 'OG desc',
                    'og_image' => 'http://3.3.3.3/cover.jpg',
                ],
            ]);

        $proxy = new ContentProxy($graby, $tagger, $this->getLogger(), $this->fetchingErrorMessage);
        $entry = $proxy->updateEntry(new Entry(new User()), 'http://0.0.0.0');

        $this->assertEquals('http://1.1.1.1', $entry->getUrl());
        $this->assertEquals('this is my title', $entry->getTitle());
        $this->assertContains('this is my content', $entry->getContent());
        $this->assertEquals('http://3.3.3.3/cover.jpg', $entry->getPreviewPicture());
        $this->assertEquals('text/html', $entry->getMimetype());
        $this->assertEquals('fr', $entry->getLanguage());
        $this->assertEquals('200', $entry->getHttpStatus());
        $this->assertEquals(4.0, $entry->getReadingTime());
        $this->assertEquals('1.1.1.1', $entry->getDomainName());
    }

    public function testWithContentAndNoOgImage()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $graby = $this->getMockBuilder('Graby\Graby')
            ->setMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => str_repeat('this is my content', 325),
                'title' => 'this is my title',
                'url' => 'http://1.1.1.1',
                'content_type' => 'text/html',
                'language' => 'fr',
                'status' => '200',
                'open_graph' => [
                    'og_title' => 'my OG title',
                    'og_description' => 'OG desc',
                    'og_image' => false,
                ],
            ]);

        $proxy = new ContentProxy($graby, $tagger, $this->getLogger(), $this->fetchingErrorMessage);
        $entry = $proxy->updateEntry(new Entry(new User()), 'http://0.0.0.0');

        $this->assertEquals('http://1.1.1.1', $entry->getUrl());
        $this->assertEquals('this is my title', $entry->getTitle());
        $this->assertContains('this is my content', $entry->getContent());
        $this->assertNull($entry->getPreviewPicture());
        $this->assertEquals('text/html', $entry->getMimetype());
        $this->assertEquals('fr', $entry->getLanguage());
        $this->assertEquals('200', $entry->getHttpStatus());
        $this->assertEquals(4.0, $entry->getReadingTime());
        $this->assertEquals('1.1.1.1', $entry->getDomainName());
    }

    public function testWithForcedContent()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $proxy = new ContentProxy((new Graby()), $tagger, $this->getLogger(), $this->fetchingErrorMessage);
        $entry = $proxy->updateEntry(
            new Entry(new User()),
            'http://0.0.0.0',
            [
                'html' => str_repeat('this is my content', 325),
                'title' => 'this is my title',
                'url' => 'http://1.1.1.1',
                'content_type' => 'text/html',
                'language' => 'fr',
                'date' => '1395635872',
                'authors' => ['Jeremy', 'Nico', 'Thomas'],
                'all_headers' => [
                    'Cache-Control' => 'no-cache',
                ],
            ]
        );

        $this->assertEquals('http://1.1.1.1', $entry->getUrl());
        $this->assertEquals('this is my title', $entry->getTitle());
        $this->assertContains('this is my content', $entry->getContent());
        $this->assertEquals('text/html', $entry->getMimetype());
        $this->assertEquals('fr', $entry->getLanguage());
        $this->assertEquals(4.0, $entry->getReadingTime());
        $this->assertEquals('1.1.1.1', $entry->getDomainName());
        $this->assertEquals('24/03/2014', $entry->getPublishedAt()->format('d/m/Y'));
        $this->assertContains('Jeremy', $entry->getPublishedBy());
        $this->assertContains('Nico', $entry->getPublishedBy());
        $this->assertContains('Thomas', $entry->getPublishedBy());
        $this->assertContains('no-cache', $entry->getHeaders());
    }

    public function testWithForcedContentAndDatetime()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $proxy = new ContentProxy((new Graby()), $tagger, $this->getLogger(), $this->fetchingErrorMessage);
        $entry = $proxy->updateEntry(
            new Entry(new User()),
            'http://0.0.0.0',
            [
                'html' => str_repeat('this is my content', 325),
                'title' => 'this is my title',
                'url' => 'http://1.1.1.1',
                'content_type' => 'text/html',
                'language' => 'fr',
                'date' => '2016-09-08T11:55:58+0200',
            ]
        );

        $this->assertEquals('http://1.1.1.1', $entry->getUrl());
        $this->assertEquals('this is my title', $entry->getTitle());
        $this->assertContains('this is my content', $entry->getContent());
        $this->assertEquals('text/html', $entry->getMimetype());
        $this->assertEquals('fr', $entry->getLanguage());
        $this->assertEquals(4.0, $entry->getReadingTime());
        $this->assertEquals('1.1.1.1', $entry->getDomainName());
        $this->assertEquals('08/09/2016', $entry->getPublishedAt()->format('d/m/Y'));
    }

    public function testWithForcedContentAndBadDate()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $proxy = new ContentProxy((new Graby()), $tagger, $logger, $this->fetchingErrorMessage);
        $entry = $proxy->updateEntry(
            new Entry(new User()),
            'http://0.0.0.0',
            [
                'html' => str_repeat('this is my content', 325),
                'title' => 'this is my title',
                'url' => 'http://1.1.1.1',
                'content_type' => 'text/html',
                'language' => 'fr',
                'date' => '01 02 2012',
            ]
        );

        $this->assertEquals('http://1.1.1.1', $entry->getUrl());
        $this->assertEquals('this is my title', $entry->getTitle());
        $this->assertContains('this is my content', $entry->getContent());
        $this->assertEquals('text/html', $entry->getMimetype());
        $this->assertEquals('fr', $entry->getLanguage());
        $this->assertEquals(4.0, $entry->getReadingTime());
        $this->assertEquals('1.1.1.1', $entry->getDomainName());
        $this->assertNull($entry->getPublishedAt());

        $records = $handler->getRecords();

        $this->assertCount(1, $records);
        $this->assertContains('Error while defining date', $records[0]['message']);
    }

    public function testTaggerThrowException()
    {
        $graby = $this->getMockBuilder('Graby\Graby')
            ->disableOriginalConstructor()
            ->getMock();

        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag')
            ->will($this->throwException(new \Exception()));

        $proxy = new ContentProxy($graby, $tagger, $this->getLogger(), $this->fetchingErrorMessage);

        $entry = $proxy->updateEntry(new Entry(new User()), 'http://0.0.0.0', [
            'html' => str_repeat('this is my content', 325),
            'title' => 'this is my title',
            'url' => 'http://1.1.1.1',
            'content_type' => 'text/html',
            'language' => 'fr',
        ]);

        $this->assertCount(0, $entry->getTags());
    }

    public function dataForCrazyHtml()
    {
        return [
            'script and comment' => [
                '<strong>Script inside:</strong> <!--[if gte IE 4]><script>alert(\'lol\');</script><![endif]--><br />',
                'lol',
            ],
            'script' => [
                '<strong>Script inside:</strong><script>alert(\'lol\');</script>',
                'script',
            ],
        ];
    }

    /**
     * @dataProvider dataForCrazyHtml
     */
    public function testWithCrazyHtmlContent($html, $escapedString)
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $proxy = new ContentProxy((new Graby()), $tagger, $this->getLogger(), $this->fetchingErrorMessage);
        $entry = $proxy->updateEntry(
            new Entry(new User()),
            'http://1.1.1.1',
            [
                'html' => $html,
                'title' => 'this is my title',
                'url' => 'http://1.1.1.1',
                'content_type' => 'text/html',
                'language' => 'fr',
                'status' => '200',
                'open_graph' => [
                    'og_title' => 'my OG title',
                    'og_description' => 'OG desc',
                    'og_image' => 'http://3.3.3.3/cover.jpg',
                ],
            ]
        );

        $this->assertEquals('http://1.1.1.1', $entry->getUrl());
        $this->assertEquals('this is my title', $entry->getTitle());
        $this->assertNotContains($escapedString, $entry->getContent());
        $this->assertEquals('http://3.3.3.3/cover.jpg', $entry->getPreviewPicture());
        $this->assertEquals('text/html', $entry->getMimetype());
        $this->assertEquals('fr', $entry->getLanguage());
        $this->assertEquals('200', $entry->getHttpStatus());
        $this->assertEquals('1.1.1.1', $entry->getDomainName());
    }

    private function getTaggerMock()
    {
        return $this->getMockBuilder(RuleBasedTagger::class)
            ->setMethods(['tag'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getLogger()
    {
        return new NullLogger();
    }
}
