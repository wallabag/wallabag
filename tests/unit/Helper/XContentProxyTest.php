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
        $http = $this->createMock(PublicHttpClient::class);
        $http->expects(self::exactly(2))->method('get')->willReturnOnConsecutiveCalls(
            ['body' => '{"code":404}'],
            ['body' => '{"author_name":"Embed author","html":"<p>Short public excerpt</p>"}']
        );
        $graby = $this->getMockBuilder(Graby::class)->setMethods(['fetchContent'])->getMock();
        $graby->expects(self::never())->method('fetchContent');
        $proxy = new ContentProxy($graby, $this->createMock(RuleBasedTagger::class), $this->createMock(RuleBasedIgnoreOriginProcessor::class), Validation::createValidator(), new NullLogger(), 'Error', false, new XPostExtractor($http));
        $proxy->updateEntry($entry, 'https://x.com/poteto/status/12345');
        self::assertSame('<p>Complete saved body and ending.</p>', $entry->getContent());
        self::assertSame('Complete saved article', $entry->getTitle());
        self::assertSame(['Saved author'], $entry->getPublishedBy());
        self::assertSame(502, $entry->getHttpStatus());
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
