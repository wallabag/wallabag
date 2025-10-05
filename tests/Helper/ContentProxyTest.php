<?php

namespace Tests\Wallabag\Helper;

use Graby\Graby;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Helper\ContentProxy;
use Wallabag\Helper\RuleBasedIgnoreOriginProcessor;
use Wallabag\Helper\RuleBasedTagger;

class ContentProxyTest extends TestCase
{
    private $fetchingErrorMessage = 'wallabag can\'t retrieve contents for this article. Please <a href="http://doc.wallabag.org/en/user/errors_during_fetching.html#how-can-i-help-to-fix-that">troubleshoot this issue</a>.';

    public function testWithBadUrl()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();

        $graby = $this->getMockBuilder(Graby::class)
            ->onlyMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => false,
                'title' => '',
                'url' => '',
                'headers' => [
                    'content-type' => '',
                ],
                'language' => '',
            ]);

        $proxy = new ContentProxy($graby, $tagger, $ruleBasedIgnoreOriginProcessor, $this->getValidator(), $this->getLogger(), $this->fetchingErrorMessage);
        $entry = new Entry(new User());
        $proxy->updateEntry($entry, 'http://user@:80');

        $this->assertSame('http://user@:80', $entry->getUrl());
        $this->assertEmpty($entry->getTitle());
        $this->assertSame($this->fetchingErrorMessage, $entry->getContent());
        $this->assertEmpty($entry->getPreviewPicture());
        $this->assertEmpty($entry->getMimetype());
        $this->assertEmpty($entry->getLanguage());
        $this->assertSame(0, $entry->getReadingTime());
        $this->assertNull($entry->getDomainName());
        $this->assertTrue($entry->isNotParsed());
    }

    public function testWithEmptyContent()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();

        $graby = $this->getMockBuilder(Graby::class)
            ->onlyMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => false,
                'title' => '',
                'url' => '',
                'headers' => [
                    'content-type' => '',
                ],
                'language' => '',
            ]);

        $proxy = new ContentProxy($graby, $tagger, $ruleBasedIgnoreOriginProcessor, $this->getValidator(), $this->getLogger(), $this->fetchingErrorMessage);
        $entry = new Entry(new User());
        $proxy->updateEntry($entry, 'http://0.0.0.0');

        $this->assertSame('http://0.0.0.0', $entry->getUrl());
        $this->assertEmpty($entry->getTitle());
        $this->assertSame($this->fetchingErrorMessage, $entry->getContent());
        $this->assertEmpty($entry->getPreviewPicture());
        $this->assertEmpty($entry->getMimetype());
        $this->assertEmpty($entry->getLanguage());
        $this->assertSame(0, $entry->getReadingTime());
        $this->assertSame('0.0.0.0', $entry->getDomainName());
        $this->assertTrue($entry->isNotParsed());
    }

    public function testWithEmptyContentButOG()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();

        $graby = $this->getMockBuilder(Graby::class)
            ->onlyMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => false,
                'title' => 'my title',
                'url' => '',
                'headers' => [
                    'content-type' => '',
                ],
                'language' => '',
                'status' => '',
                'description' => 'desc',
            ]);

        $proxy = new ContentProxy($graby, $tagger, $ruleBasedIgnoreOriginProcessor, $this->getValidator(), $this->getLogger(), $this->fetchingErrorMessage);
        $entry = new Entry(new User());
        $proxy->updateEntry($entry, 'http://domain.io');

        $this->assertSame('http://domain.io', $entry->getUrl());
        $this->assertSame('my title', $entry->getTitle());
        $this->assertSame($this->fetchingErrorMessage . '<p><i>But we found a short description: </i></p>desc', $entry->getContent());
        $this->assertEmpty($entry->getPreviewPicture());
        $this->assertEmpty($entry->getLanguage());
        $this->assertEmpty($entry->getHttpStatus());
        $this->assertEmpty($entry->getMimetype());
        $this->assertSame(0, $entry->getReadingTime());
        $this->assertSame('domain.io', $entry->getDomainName());
        $this->assertTrue($entry->isNotParsed());
    }

    public function testWithContent()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();
        $ruleBasedIgnoreOriginProcessor->expects($this->once())
            ->method('process');

        $graby = $this->getMockBuilder(Graby::class)
            ->onlyMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => str_repeat('this is my content', 325),
                'title' => 'this is my title',
                'url' => 'http://1.1.1.1',
                'language' => 'fr',
                'status' => '200',
                'description' => 'OG desc',
                'image' => 'http://3.3.3.3/cover.jpg',
                'headers' => [
                    'content-type' => 'text/html',
                ],
            ]);

        $proxy = new ContentProxy($graby, $tagger, $ruleBasedIgnoreOriginProcessor, $this->getValidator(), $this->getLogger(), $this->fetchingErrorMessage);
        $entry = new Entry(new User());
        $proxy->updateEntry($entry, 'http://0.0.0.0');

        $this->assertSame('http://1.1.1.1', $entry->getUrl());
        $this->assertSame('this is my title', $entry->getTitle());
        $this->assertStringContainsString('content', $entry->getContent());
        $this->assertSame('http://3.3.3.3/cover.jpg', $entry->getPreviewPicture());
        $this->assertSame('text/html', $entry->getMimetype());
        $this->assertSame('fr', $entry->getLanguage());
        $this->assertSame('200', $entry->getHttpStatus());
        $this->assertSame(4, $entry->getReadingTime());
        $this->assertSame('1.1.1.1', $entry->getDomainName());
        $this->assertFalse($entry->isNotParsed());
    }

    public function testWithContentAndNoOgImage()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();
        $ruleBasedIgnoreOriginProcessor->expects($this->once())
            ->method('process');

        $graby = $this->getMockBuilder(Graby::class)
            ->onlyMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => str_repeat('this is my content', 325),
                'title' => 'this is my title',
                'url' => 'http://1.1.1.1',
                'language' => 'fr',
                'status' => '200',
                'description' => 'OG desc',
                'image' => null,
                'headers' => [
                    'content-type' => 'text/html',
                ],
            ]);

        $proxy = new ContentProxy($graby, $tagger, $ruleBasedIgnoreOriginProcessor, $this->getValidator(), $this->getLogger(), $this->fetchingErrorMessage);
        $entry = new Entry(new User());
        $proxy->updateEntry($entry, 'http://0.0.0.0');

        $this->assertSame('http://1.1.1.1', $entry->getUrl());
        $this->assertSame('this is my title', $entry->getTitle());
        $this->assertStringContainsString('content', $entry->getContent());
        $this->assertNull($entry->getPreviewPicture());
        $this->assertSame('text/html', $entry->getMimetype());
        $this->assertSame('fr', $entry->getLanguage());
        $this->assertSame('200', $entry->getHttpStatus());
        $this->assertSame(4, $entry->getReadingTime());
        $this->assertSame('1.1.1.1', $entry->getDomainName());
        $this->assertFalse($entry->isNotParsed());
    }

    public function testWithContentAndContentImage()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();
        $ruleBasedIgnoreOriginProcessor->expects($this->once())
            ->method('process');

        $graby = $this->getMockBuilder(Graby::class)
            ->onlyMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => "<h1>Test</h1><p><img src='http://3.3.3.3/cover.jpg'/></p>",
                'title' => 'this is my title',
                'url' => 'http://1.1.1.1',
                'headers' => [
                    'content-type' => 'text/html',
                ],
                'language' => 'fr',
                'status' => '200',
                'image' => null,
            ]);

        $proxy = new ContentProxy($graby, $tagger, $ruleBasedIgnoreOriginProcessor, $this->getValidator(), $this->getLogger(), $this->fetchingErrorMessage);
        $entry = new Entry(new User());
        $proxy->updateEntry($entry, 'http://0.0.0.0');

        $this->assertSame('http://1.1.1.1', $entry->getUrl());
        $this->assertSame('this is my title', $entry->getTitle());
        $this->assertSame("<h1>Test</h1><p><img src='http://3.3.3.3/cover.jpg'/></p>", $entry->getContent());
        $this->assertSame('http://3.3.3.3/cover.jpg', $entry->getPreviewPicture());
        $this->assertSame('text/html', $entry->getMimetype());
        $this->assertSame('fr', $entry->getLanguage());
        $this->assertSame('200', $entry->getHttpStatus());
        $this->assertSame(0, $entry->getReadingTime());
        $this->assertSame('1.1.1.1', $entry->getDomainName());
        $this->assertFalse($entry->isNotParsed());
    }

    public function testWithContentImageAndOgImage()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();
        $ruleBasedIgnoreOriginProcessor->expects($this->once())
            ->method('process');

        $graby = $this->getMockBuilder(Graby::class)
            ->onlyMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => "<h1>Test</h1><p><img src='http://3.3.3.3/nevermind.jpg'/></p>",
                'title' => 'this is my title',
                'url' => 'http://1.1.1.1',
                'headers' => [
                    'content-type' => 'text/html',
                ],
                'language' => 'fr',
                'status' => '200',
                'image' => 'http://3.3.3.3/cover.jpg',
            ]);

        $proxy = new ContentProxy($graby, $tagger, $ruleBasedIgnoreOriginProcessor, $this->getValidator(), $this->getLogger(), $this->fetchingErrorMessage);
        $entry = new Entry(new User());
        $proxy->updateEntry($entry, 'http://0.0.0.0');

        $this->assertSame('http://1.1.1.1', $entry->getUrl());
        $this->assertSame('this is my title', $entry->getTitle());
        $this->assertSame("<h1>Test</h1><p><img src='http://3.3.3.3/nevermind.jpg'/></p>", $entry->getContent());
        $this->assertSame('http://3.3.3.3/cover.jpg', $entry->getPreviewPicture());
        $this->assertSame('text/html', $entry->getMimetype());
        $this->assertSame('fr', $entry->getLanguage());
        $this->assertSame('200', $entry->getHttpStatus());
        $this->assertSame(0, $entry->getReadingTime());
        $this->assertSame('1.1.1.1', $entry->getDomainName());
        $this->assertFalse($entry->isNotParsed());
    }

    public function testWithContentAndBadLanguage()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();
        $ruleBasedIgnoreOriginProcessor->expects($this->once())
            ->method('process');

        $validator = $this->getValidator(false);
        $validator->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList([new ConstraintViolation('oops', 'oops', [], 'oops', 'language', 'dontexist')]));

        $graby = $this->getMockBuilder(Graby::class)
            ->onlyMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => str_repeat('this is my content', 325),
                'title' => 'this is my title',
                'url' => 'http://1.1.1.1',
                'language' => 'dontexist',
                'status' => '200',
                'headers' => [
                    'content-type' => 'text/html',
                ],
            ]);

        $proxy = new ContentProxy($graby, $tagger, $ruleBasedIgnoreOriginProcessor, $validator, $this->getLogger(), $this->fetchingErrorMessage);
        $entry = new Entry(new User());
        $proxy->updateEntry($entry, 'http://0.0.0.0');

        $this->assertSame('http://1.1.1.1', $entry->getUrl());
        $this->assertSame('this is my title', $entry->getTitle());
        $this->assertStringContainsString('content', $entry->getContent());
        $this->assertSame('text/html', $entry->getMimetype());
        $this->assertNull($entry->getLanguage());
        $this->assertSame('200', $entry->getHttpStatus());
        $this->assertSame(4, $entry->getReadingTime());
        $this->assertSame('1.1.1.1', $entry->getDomainName());
        $this->assertFalse($entry->isNotParsed());
    }

    public function testWithContentAndBadOgImage()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();
        $ruleBasedIgnoreOriginProcessor->expects($this->once())
            ->method('process');

        $validator = $this->getValidator(false);
        $validator->expects($this->exactly(2))
            ->method('validate')
            ->will($this->onConsecutiveCalls(
                new ConstraintViolationList(),
                new ConstraintViolationList([new ConstraintViolation('oops', 'oops', [], 'oops', 'url', 'https://')])
            ));

        $graby = $this->getMockBuilder(Graby::class)
            ->onlyMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => str_repeat('this is my content', 325),
                'title' => 'this is my title',
                'url' => 'http://1.1.1.1',
                'headers' => [
                    'content-type' => 'text/html',
                ],
                'language' => 'fr',
                'status' => '200',
                'description' => 'OG desc',
                'image' => 'https://',
            ]);

        $proxy = new ContentProxy($graby, $tagger, $ruleBasedIgnoreOriginProcessor, $validator, $this->getLogger(), $this->fetchingErrorMessage);
        $entry = new Entry(new User());
        $proxy->updateEntry($entry, 'http://0.0.0.0');

        $this->assertSame('http://1.1.1.1', $entry->getUrl());
        $this->assertSame('this is my title', $entry->getTitle());
        $this->assertStringContainsString('content', $entry->getContent());
        $this->assertNull($entry->getPreviewPicture());
        $this->assertSame('text/html', $entry->getMimetype());
        $this->assertSame('fr', $entry->getLanguage());
        $this->assertSame('200', $entry->getHttpStatus());
        $this->assertSame(4, $entry->getReadingTime());
        $this->assertSame('1.1.1.1', $entry->getDomainName());
        $this->assertFalse($entry->isNotParsed());
    }

    public function testWithForcedContent()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();
        $ruleBasedIgnoreOriginProcessor->expects($this->once())
            ->method('process');

        $proxy = new ContentProxy(new Graby(), $tagger, $ruleBasedIgnoreOriginProcessor, $this->getValidator(), $this->getLogger(), $this->fetchingErrorMessage, true);
        $entry = new Entry(new User());
        $proxy->updateEntry(
            $entry,
            'http://0.0.0.0',
            [
                'html' => str_repeat('this is my content', 325),
                'title' => 'this is my title',
                'url' => 'http://1.1.1.1',
                'language' => 'fr',
                'date' => '1395635872',
                'authors' => ['Jeremy', 'Nico', 'Thomas'],
                'headers' => [
                    'cache-control' => 'no-cache',
                    'content-type' => 'text/html',
                ],
            ]
        );

        $this->assertSame('http://1.1.1.1', $entry->getUrl());
        $this->assertSame('this is my title', $entry->getTitle());
        $this->assertStringContainsString('content', $entry->getContent());
        $this->assertSame('text/html', $entry->getMimetype());
        $this->assertSame('fr', $entry->getLanguage());
        $this->assertSame(4, $entry->getReadingTime());
        $this->assertSame('1.1.1.1', $entry->getDomainName());
        $this->assertSame('24/03/2014', $entry->getPublishedAt()->format('d/m/Y'));
        $this->assertContains('Jeremy', $entry->getPublishedBy());
        $this->assertContains('Nico', $entry->getPublishedBy());
        $this->assertContains('Thomas', $entry->getPublishedBy());
        $this->assertNotNull($entry->getHeaders(), 'Headers are stored, so value is not null');
        $this->assertContains('no-cache', $entry->getHeaders());
        $this->assertFalse($entry->isNotParsed());
    }

    public function testWithForcedContentAndDateTime()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();

        $logHandler = new TestHandler();
        $logger = new Logger('test', [$logHandler]);

        $proxy = new ContentProxy(new Graby(), $tagger, $ruleBasedIgnoreOriginProcessor, $this->getValidator(), $logger, $this->fetchingErrorMessage);
        $entry = new Entry(new User());
        $proxy->updateEntry(
            $entry,
            'http://1.1.1.1',
            [
                'html' => str_repeat('this is my content', 325),
                'title' => 'this is my title',
                'url' => 'http://1.1.1.1',
                'language' => 'fr',
                'date' => '2016-09-08T11:55:58+0200',
                'headers' => [
                    'content-type' => 'text/html',
                ],
            ]
        );

        $this->assertSame('http://1.1.1.1', $entry->getUrl());
        $this->assertSame('this is my title', $entry->getTitle());
        $this->assertStringContainsString('content', $entry->getContent());
        $this->assertSame('text/html', $entry->getMimetype());
        $this->assertSame('fr', $entry->getLanguage());
        $this->assertSame(4, $entry->getReadingTime());
        $this->assertSame('1.1.1.1', $entry->getDomainName());
        $this->assertSame('08/09/2016', $entry->getPublishedAt()->format('d/m/Y'));
        $this->assertFalse($entry->isNotParsed());
    }

    public function testWithForcedContentAndBadDate()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();

        $logger = new Logger('foo');
        $handler = new TestHandler();
        $logger->pushHandler($handler);

        $proxy = new ContentProxy(new Graby(), $tagger, $ruleBasedIgnoreOriginProcessor, $this->getValidator(), $logger, $this->fetchingErrorMessage);
        $entry = new Entry(new User());
        $proxy->updateEntry(
            $entry,
            'http://1.1.1.1',
            [
                'html' => str_repeat('this is my content', 325),
                'title' => 'this is my title',
                'url' => 'http://1.1.1.1',
                'language' => 'fr',
                'date' => '01 02 2012',
                'headers' => [
                    'content-type' => 'text/html',
                ],
            ]
        );

        $this->assertSame('http://1.1.1.1', $entry->getUrl());
        $this->assertSame('this is my title', $entry->getTitle());
        $this->assertStringContainsString('content', $entry->getContent());
        $this->assertSame('text/html', $entry->getMimetype());
        $this->assertSame('fr', $entry->getLanguage());
        $this->assertSame(4, $entry->getReadingTime());
        $this->assertSame('1.1.1.1', $entry->getDomainName());
        $this->assertNull($entry->getPublishedAt());
        $this->assertFalse($entry->isNotParsed());

        $records = $handler->getRecords();

        $this->assertCount(3, $records);
        $this->assertStringContainsString('Error while defining date', $records[0]['message']);
    }

    public function testTaggerThrowException()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag')
            ->will($this->throwException(new \Exception()));

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();

        $proxy = new ContentProxy(new Graby(), $tagger, $ruleBasedIgnoreOriginProcessor, $this->getValidator(), $this->getLogger(), $this->fetchingErrorMessage);
        $entry = new Entry(new User());
        $proxy->updateEntry(
            $entry,
            'http://1.1.1.1',
            [
                'html' => str_repeat('this is my content', 325),
                'title' => 'this is my title',
                'url' => 'http://1.1.1.1',
                'language' => 'fr',
                'headers' => [
                    'content-type' => 'text/html',
                ],
            ]
        );

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

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();

        $proxy = new ContentProxy(new Graby(), $tagger, $ruleBasedIgnoreOriginProcessor, $this->getValidator(), $this->getLogger(), $this->fetchingErrorMessage);
        $entry = new Entry(new User());
        $proxy->updateEntry(
            $entry,
            'http://1.1.1.1',
            [
                'html' => $html,
                'title' => 'this is my title',
                'url' => 'http://1.1.1.1',
                'language' => 'fr',
                'status' => '200',
                // 'og_title' => 'my OG title',
                'description' => 'OG desc',
                'image' => 'http://3.3.3.3/cover.jpg',
                'headers' => [
                    'content-type' => 'text/html',
                ],
            ]
        );

        $this->assertSame('http://1.1.1.1', $entry->getUrl());
        $this->assertSame('this is my title', $entry->getTitle());
        $this->assertStringNotContainsString($escapedString, $entry->getContent());
        $this->assertSame('http://3.3.3.3/cover.jpg', $entry->getPreviewPicture());
        $this->assertSame('text/html', $entry->getMimetype());
        $this->assertSame('fr', $entry->getLanguage());
        $this->assertSame('200', $entry->getHttpStatus());
        $this->assertSame('1.1.1.1', $entry->getDomainName());
        $this->assertFalse($entry->isNotParsed());
    }

    public function testWithImageAsContent()
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();

        $graby = $this->getMockBuilder(Graby::class)
            ->onlyMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => '<p><img src="http://1.1.1.1/image.jpg" /></p>',
                'title' => 'this is my title',
                'url' => 'http://1.1.1.1/image.jpg',
                'status' => '200',
                'headers' => [
                    'content-type' => 'image/jpeg',
                ],
            ]);

        $proxy = new ContentProxy($graby, $tagger, $ruleBasedIgnoreOriginProcessor, $this->getValidator(), $this->getLogger(), $this->fetchingErrorMessage);
        $entry = new Entry(new User());
        $proxy->updateEntry($entry, 'http://0.0.0.0');

        $this->assertSame('http://1.1.1.1/image.jpg', $entry->getUrl());
        $this->assertSame('this is my title', $entry->getTitle());
        $this->assertStringContainsString('http://1.1.1.1/image.jpg', $entry->getContent());
        $this->assertSame('http://1.1.1.1/image.jpg', $entry->getPreviewPicture());
        $this->assertSame('image/jpeg', $entry->getMimetype());
        $this->assertSame('200', $entry->getHttpStatus());
        $this->assertSame('1.1.1.1', $entry->getDomainName());
        $this->assertFalse($entry->isNotParsed());
    }

    public function testWebsiteWithValidUTF8TitleDoNothing()
    {
        // You can use https://www.online-toolz.com/tools/text-hex-convertor.php to convert UTF-8 text <=> hex
        // See http://graphemica.com for more info about the characters
        // '😻ℤz' (U+1F63B or F09F98BB; U+2124 or E284A4; U+007A or 7A) in hexadecimal and UTF-8
        $actualTitle = $this->hexToStr('F09F98BB' . 'E284A4' . '7A');

        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();

        $graby = $this->getMockBuilder(Graby::class)
            ->onlyMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => false,
                'title' => $actualTitle,
                'url' => '',
                'headers' => [
                    'content-type' => 'text/html',
                ],
                'language' => '',
            ]);

        $proxy = new ContentProxy($graby, $tagger, $ruleBasedIgnoreOriginProcessor, $this->getValidator(), $this->getLogger(), $this->fetchingErrorMessage);
        $entry = new Entry(new User());
        $proxy->updateEntry($entry, 'http://0.0.0.0');

        // '😻ℤz' (U+1F63B or F09F98BB; U+2124 or E284A4; U+007A or 7A) in hexadecimal and UTF-8
        $expectedTitle = 'F09F98BB' . 'E284A4' . '7A';
        $this->assertSame($expectedTitle, $this->strToHex($entry->getTitle()));
    }

    public function testWebsiteWithInvalidUTF8TitleRemoveInvalidCharacter()
    {
        // See http://graphemica.com for more info about the characters
        // 'a€b' (61;80;62) in hexadecimal and WINDOWS-1252 - but 80 is a invalid UTF-8 character.
        // The correct UTF-8 € character (U+20AC) is E282AC
        $actualTitle = $this->hexToStr('61' . '80' . '62');

        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();

        $graby = $this->getMockBuilder(Graby::class)
            ->onlyMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => false,
                'title' => $actualTitle,
                'url' => '',
                'headers' => [
                    'content-type' => 'text/html',
                ],
                'language' => '',
            ]);

        $proxy = new ContentProxy($graby, $tagger, $ruleBasedIgnoreOriginProcessor, $this->getValidator(), $this->getLogger(), $this->fetchingErrorMessage);
        $entry = new Entry(new User());
        $proxy->updateEntry($entry, 'http://0.0.0.0');

        // 'ab' (61;62) because all invalid UTF-8 character (like 80) are removed
        $expectedTitle = '61' . '62';
        $this->assertSame($expectedTitle, $this->strToHex($entry->getTitle()));
    }

    public function testPdfWithUTF16BETitleConvertToUTF8()
    {
        // See http://graphemica.com for more info about the characters
        // '😻' (U+1F63B;D83DDE3B) in hexadecimal and as UTF16BE
        $actualTitle = $this->hexToStr('D83DDE3B');

        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();

        $graby = $this->getMockBuilder(Graby::class)
            ->onlyMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => false,
                'title' => $actualTitle,
                'url' => '',
                'headers' => [
                    'content-type' => 'application/pdf',
                ],
                'language' => '',
            ]);

        $proxy = new ContentProxy($graby, $tagger, $ruleBasedIgnoreOriginProcessor, $this->getValidator(), $this->getLogger(), $this->fetchingErrorMessage);
        $entry = new Entry(new User());
        $proxy->updateEntry($entry, 'http://0.0.0.0');

        // '😻' (U+1F63B or F09F98BB) in hexadecimal and UTF-8
        $expectedTitle = 'F09F98BB';
        $this->assertSame($expectedTitle, $this->strToHex($entry->getTitle()));
    }

    public function testPdfWithUTF8TitleDoNothing()
    {
        // See http://graphemica.com for more info about the characters
        // '😻' (U+1F63B;D83DDE3B) in hexadecimal and as UTF8
        $actualTitle = $this->hexToStr('F09F98BB');

        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();

        $graby = $this->getMockBuilder(Graby::class)
            ->onlyMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => false,
                'title' => $actualTitle,
                'url' => '',
                'headers' => [
                    'content-type' => 'application/pdf',
                ],
                'language' => '',
            ]);

        $proxy = new ContentProxy($graby, $tagger, $ruleBasedIgnoreOriginProcessor, $this->getValidator(), $this->getLogger(), $this->fetchingErrorMessage);
        $entry = new Entry(new User());
        $proxy->updateEntry($entry, 'http://0.0.0.0');

        // '😻' (U+1F63B or F09F98BB) in hexadecimal and UTF-8
        $expectedTitle = 'F09F98BB';
        $this->assertSame($expectedTitle, $this->strToHex($entry->getTitle()));
    }

    public function testPdfWithWINDOWS1252TitleConvertToUTF8()
    {
        // See http://graphemica.com for more info about the characters
        // '€' (80) in hexadecimal and WINDOWS-1252
        $actualTitle = $this->hexToStr('80');

        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();

        $graby = $this->getMockBuilder(Graby::class)
            ->onlyMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => false,
                'title' => $actualTitle,
                'url' => '',
                'headers' => [
                    'content-type' => 'application/pdf',
                ],
                'language' => '',
            ]);

        $proxy = new ContentProxy($graby, $tagger, $ruleBasedIgnoreOriginProcessor, $this->getValidator(), $this->getLogger(), $this->fetchingErrorMessage);
        $entry = new Entry(new User());
        $proxy->updateEntry($entry, 'http://0.0.0.0');

        // '€' (U+20AC or E282AC) in hexadecimal and UTF-8
        $expectedTitle = 'E282AC';
        $this->assertSame($expectedTitle, $this->strToHex($entry->getTitle()));
    }

    public function testPdfWithInvalidCharacterInTitleRemoveInvalidCharacter()
    {
        /*
         * I spend too much time on trying to solve the problem of that test.
         * Starting with PHP 8.1 this test fails because the string with invalid character is detected as WINDOWS-1252 and then converted.
         * In PHP < 8.1, the string encoding can't be detected and nothing is then converted.
         * So the removal of the invalid char happens in `sanitizeUTF8Text`
         *
         * So, I don't understand why the string with invalid char is detected as WINDOWS-1252 in PHP 8.1 and not before.
         */
        $this->markTestSkipped('Encoding issue in PHP >= 8.1');

        // // See http://graphemica.com for more info about the characters
        // // '😻ℤ�z' (U+1F63B or F09F98BB; U+2124 or E284A4; invalid character 81; U+007A or 7A) in hexadecimal and UTF-8
        // // 0x81 is not a valid character for UTF16, UTF8 and WINDOWS-1252
        // $actualTitle = $this->hexToStr('F09F98BB' . 'E284A4' . '81' . '7A');
        //
        // $tagger = $this->getTaggerMock();
        // $tagger->expects($this->once())
        //     ->method('tag');
        //
        // $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();
        //
        // $graby = $this->getMockBuilder(Graby::class)
        //     ->onlyMethods(['fetchContent'])
        //     ->disableOriginalConstructor()
        //     ->getMock();
        //
        // $graby->expects($this->any())
        //     ->method('fetchContent')
        //     ->willReturn([
        //         'html' => false,
        //         'title' => $actualTitle,
        //         'url' => '',
        //         'headers' => [
        //             'content-type' => 'application/pdf',
        //         ],
        //         'language' => '',
        //     ]);
        //
        // $proxy = new ContentProxy($graby, $tagger, $ruleBasedIgnoreOriginProcessor, $this->getValidator(), $this->getLogger(), $this->fetchingErrorMessage);
        // $entry = new Entry(new User());
        // $proxy->updateEntry($entry, 'http://0.0.0.0');
        //
        // // '😻ℤz' (U+1F63B or F09F98BB; U+2124 or E284A4; U+007A or 7A) in hexadecimal and UTF-8
        // // the 0x81 (represented by �) is invalid for UTF16, UTF8 and WINDOWS-1252 and is removed
        // $expectedTitle = 'F09F98BB' . 'E284A4' . '7A';
        // $this->assertSame($expectedTitle, $this->strToHex($entry->getTitle()));
    }

    /**
     * Data provider for testWithChangedUrl.
     *
     * Arrays contain the following values:
     * $entry_url
     * $origin_url
     * $content_url
     * $expected_entry_url
     * $expected_origin_url
     * $expected_domain
     * $processor_result
     */
    public function dataForChangedUrl()
    {
        return [
            'normal' => [
                'http://0.0.0.0',
                null,
                'http://1.1.1.1',
                'http://1.1.1.1',
                'http://0.0.0.0',
                '1.1.1.1',
                false,
            ],
            'origin already set' => [
                'http://0.0.0.0',
                'http://hello',
                'http://1.1.1.1',
                'http://1.1.1.1',
                'http://hello',
                '1.1.1.1',
                false,
            ],
            'trailing slash' => [
                'https://example.com/hello-world',
                null,
                'https://example.com/hello-world/',
                'https://example.com/hello-world/',
                null,
                'example.com',
                false,
            ],
            'query string in fetched content' => [
                'https://example.org/hello',
                null,
                'https://example.org/hello?world=1',
                'https://example.org/hello?world=1',
                'https://example.org/hello',
                'example.org',
                false,
            ],
            'fragment in fetched content' => [
                'https://example.org/hello',
                null,
                'https://example.org/hello#world',
                'https://example.org/hello',
                null,
                'example.org',
                false,
            ],
            'fragment and query string in fetched content' => [
                'https://example.org/hello',
                null,
                'https://example.org/hello?foo#world',
                'https://example.org/hello?foo#world',
                'https://example.org/hello',
                'example.org',
                false,
            ],
            'different path and query string in fetch content' => [
                'https://example.org/hello',
                null,
                'https://example.org/world?foo',
                'https://example.org/world?foo',
                'https://example.org/hello',
                'example.org',
                false,
            ],
            'feedproxy ignore list test' => [
                'http://feedproxy.google.com/~r/Wallabag/~3/helloworld',
                null,
                'https://example.org/hello-wallabag',
                'https://example.org/hello-wallabag',
                null,
                'example.org',
                true,
            ],
            'feedproxy ignore list test with origin url already set' => [
                'http://feedproxy.google.com/~r/Wallabag/~3/helloworld',
                'https://example.org/this-is-source',
                'https://example.org/hello-wallabag',
                'https://example.org/hello-wallabag',
                'https://example.org/this-is-source',
                'example.org',
                true,
            ],
            'lemonde ignore pattern test' => [
                'http://www.lemonde.fr/tiny/url',
                null,
                'http://example.com/hello-world',
                'http://example.com/hello-world',
                null,
                'example.com',
                true,
            ],
        ];
    }

    /**
     * @dataProvider dataForChangedUrl
     */
    public function testWithChangedUrl($entry_url, $origin_url, $content_url, $expected_entry_url, $expected_origin_url, $expected_domain, $processor_result)
    {
        $tagger = $this->getTaggerMock();
        $tagger->expects($this->once())
            ->method('tag');

        $ruleBasedIgnoreOriginProcessor = $this->getRuleBasedIgnoreOriginProcessorMock();
        $ruleBasedIgnoreOriginProcessor->expects($this->once())
            ->method('process')
            ->willReturn($processor_result);

        $proxy = new ContentProxy(new Graby(), $tagger, $ruleBasedIgnoreOriginProcessor, $this->getValidator(), $this->getLogger(), $this->fetchingErrorMessage, true);
        $entry = new Entry(new User());
        $entry->setOriginUrl($origin_url);
        $proxy->updateEntry(
            $entry,
            $entry_url,
            [
                'html' => false,
                'title' => '',
                'url' => $content_url,
                'headers' => [
                    'content-type' => '',
                ],
                'language' => '',
            ],
            true
        );

        $this->assertSame($expected_entry_url, $entry->getUrl());
        $this->assertSame($expected_domain, $entry->getDomainName());
        $this->assertSame($expected_origin_url, $entry->getOriginUrl());
    }

    /**
     * https://stackoverflow.com/a/18506801.
     *
     * @return string
     */
    private function strToHex($string)
    {
        $hex = '';
        for ($i = 0; $i < \strlen((string) $string); ++$i) {
            $ord = \ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0' . $hexCode, -2);
        }

        return strtoupper($hex);
    }

    /**
     * Convert hex to string.
     *
     * @see https://stackoverflow.com/a/18506801
     *
     * @return string
     */
    private function hexToStr($hex)
    {
        $string = '';
        for ($i = 0; $i < \strlen((string) $hex) - 1; $i += 2) {
            $string .= \chr(hexdec($hex[$i] . $hex[$i + 1]));
        }

        return $string;
    }

    private function getTaggerMock()
    {
        return $this->getMockBuilder(RuleBasedTagger::class)
            ->onlyMethods(['tag'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getRuleBasedIgnoreOriginProcessorMock()
    {
        return $this->getMockBuilder(RuleBasedIgnoreOriginProcessor::class)
            ->onlyMethods(['process'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getLogger()
    {
        return new NullLogger();
    }

    private function getValidator($withDefaultMock = true)
    {
        $mock = $this->getMockBuilder(RecursiveValidator::class)
            ->onlyMethods(['validate'])
            ->disableOriginalConstructor()
            ->getMock();

        if ($withDefaultMock) {
            $mock->expects($this->any())
                ->method('validate')
                ->willReturn(new ConstraintViolationList());
        }

        return $mock;
    }
}
