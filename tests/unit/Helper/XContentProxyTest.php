<?php

namespace Wallabag\Tests\Unit\Helper;

use Graby\Graby;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Validator\Validation;
use Wallabag\Entity\Config;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Helper\ContentProxy;
use Wallabag\Helper\PublicHttpClient;
use Wallabag\Helper\RuleBasedIgnoreOriginProcessor;
use Wallabag\Helper\RuleBasedTagger;
use Wallabag\Helper\XPostExtractor;

class XContentProxyTest extends TestCase
{
    public function testNormalSaveUsesPublicSnapshotInsteadOfGrabyNetwork(): void
    {
        $entry = new Entry(new User());
        $this->proxy()->updateEntry($entry, 'https://x.com/poteto/status/2069824386283319343');
        self::assertSame(206, $entry->getHttpStatus());
        self::assertStringContainsString('Public text', $entry->getContent());
        self::assertStringContainsString('Partial X capture', $entry->getContent());
        self::assertSame(['lauren'], $entry->getPublishedBy());
    }

    public function testFailedReloadRetainsPreviousContent(): void
    {
        $entry = new Entry(new User());
        $entry->setContent('Saved snapshot');
        $this->proxy(true)->updateEntry($entry, 'https://x.com/poteto/status/2069824386283319343');
        self::assertSame('Saved snapshot', $entry->getContent());
        self::assertSame(502, $entry->getHttpStatus());
    }

    public function testArticleReaderFailureDoesNotReplaceSavedArticleWithEmbed(): void
    {
        $user = new User();
        $config = new Config($user);
        $config->setXArticleReaderEnabled(true);
        $user->setConfig($config);
        $entry = new Entry($user);
        $entry->setTitle('Complete saved article');
        $entry->setContent('<p>Complete saved body and ending.</p>');
        $entry->setPublishedBy(['Saved author']);
        $entry->setHttpStatus(200);
        $entry->setXCaptureType('article');
        $http = $this->createMock(PublicHttpClient::class);
        $http->expects(self::exactly(4))->method('get')->willReturnOnConsecutiveCalls(
            ['body' => '{"code":404}'],
            ['body' => '{"author_name":"Embed author","html":"<p>Short public excerpt</p>"}'],
            ['body' => '{"code":404}'],
            ['body' => '{"author_name":"Embed author","html":"<p>Another public excerpt</p>"}']
        );
        $graby = $this->getMockBuilder(Graby::class)->setMethods(['fetchContent'])->getMock();
        $graby->expects(self::never())->method('fetchContent');
        $proxy = new ContentProxy($graby, $this->createMock(RuleBasedTagger::class), $this->createMock(RuleBasedIgnoreOriginProcessor::class), Validation::createValidator(), new NullLogger(), 'Error', false, new XPostExtractor($http));
        $proxy->updateEntry($entry, 'https://x.com/poteto/status/12345');
        $proxy->updateEntry($entry, 'https://x.com/poteto/status/12345');
        self::assertSame('<p>Complete saved body and ending.</p>', $entry->getContent());
        self::assertSame('Complete saved article', $entry->getTitle());
        self::assertSame(['Saved author'], $entry->getPublishedBy());
        self::assertSame('article', $entry->getXCaptureType());
        self::assertSame(502, $entry->getHttpStatus());
    }

    public function testUnavailableBookmarkRecoversToSuccessfulPartialEmbed(): void
    {
        $http = $this->createMock(PublicHttpClient::class);
        $http->expects(self::exactly(4))->method('get')->willReturnOnConsecutiveCalls(
            ['body' => '{"code":404}'], ['body' => '{"error":"unavailable"}'],
            ['body' => '{"code":404}'], ['body' => '{"author_name":"Recovered author","html":"<p>Recovered public post</p>"}']
        );
        $entry = $this->optedInEntry();
        $proxy = $this->withHttp($http);
        $proxy->updateEntry($entry, 'https://x.com/person/status/12345');
        self::assertSame(502, $entry->getHttpStatus());
        self::assertSame('unavailable', $entry->getXCaptureType());
        self::assertNotEmpty($entry->getContent());
        $proxy->updateEntry($entry, 'https://x.com/person/status/12345');
        self::assertSame(206, $entry->getHttpStatus());
        self::assertSame('post', $entry->getXCaptureType());
        self::assertSame(['Recovered author'], $entry->getPublishedBy());
        self::assertStringContainsString('Recovered public post', $entry->getContent());
        self::assertStringNotContainsString('not a captured post', $entry->getContent());
    }

    public function testOrdinaryPostWithoutArticleCanReloadNewEmbed(): void
    {
        $http = $this->createMock(PublicHttpClient::class);
        $ordinary = ['body' => '{"code":200,"status":{"id":"12345","author":{"name":"Author"}}}'];
        $http->expects(self::exactly(4))->method('get')->willReturnOnConsecutiveCalls(
            $ordinary, ['body' => '{"author_name":"Old author","html":"<p>Old post text</p>"}'],
            $ordinary, ['body' => '{"author_name":"New author","html":"<p>Updated post text</p>"}']
        );
        $entry = $this->optedInEntry();
        $proxy = $this->withHttp($http);
        $proxy->updateEntry($entry, 'https://x.com/person/status/12345');
        self::assertSame('post', $entry->getXCaptureType());
        $proxy->updateEntry($entry, 'https://x.com/person/status/12345');
        self::assertSame(206, $entry->getHttpStatus());
        self::assertStringContainsString('Updated post text', $entry->getContent());
        self::assertStringNotContainsString('Old post text', $entry->getContent());
        self::assertSame(['New author'], $entry->getPublishedBy());
    }

    private function optedInEntry(): Entry
    {
        $user = new User();
        $config = new Config($user);
        $config->setXArticleReaderEnabled(true);
        $user->setConfig($config);

        return new Entry($user);
    }

    private function withHttp(PublicHttpClient $http): ContentProxy
    {
        $graby = $this->getMockBuilder(Graby::class)->setMethods(['fetchContent'])->getMock();
        $graby->expects(self::never())->method('fetchContent');

        return new ContentProxy($graby, $this->createMock(RuleBasedTagger::class), $this->createMock(RuleBasedIgnoreOriginProcessor::class), Validation::createValidator(), new NullLogger(), 'Error', false, new XPostExtractor($http));
    }

    private function proxy(bool $fail = false): ContentProxy
    {
        $http = $this->createMock(PublicHttpClient::class);
        if ($fail) {
            $http->method('get')->willThrowException(new \RuntimeException('HTTP 403'));
        } else {
            $http->method('get')->willReturn(['body' => '{"author_name":"lauren","html":"<blockquote><p>Public text</p></blockquote>"}']);
        }
        $graby = $this->getMockBuilder(Graby::class)->setMethods(['fetchContent'])->getMock();
        $graby->expects(self::never())->method('fetchContent');

        return new ContentProxy($graby, $this->createMock(RuleBasedTagger::class), $this->createMock(RuleBasedIgnoreOriginProcessor::class), Validation::createValidator(), new NullLogger(), 'Error', false, new XPostExtractor($http));
    }
}
