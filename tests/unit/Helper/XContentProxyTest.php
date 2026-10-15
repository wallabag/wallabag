<?php

namespace Wallabag\Tests\Unit\Helper;

use Graby\Graby;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Validator\Validation;
use Wallabag\Entity\Entry;
use Wallabag\Helper\ContentProxy;
use Wallabag\Helper\PublicHttpClient;
use Wallabag\Helper\RuleBasedIgnoreOriginProcessor;
use Wallabag\Helper\RuleBasedTagger;
use Wallabag\Helper\XPostExtractor;
use Wallabag\Entity\User;

class XContentProxyTest extends TestCase
{
    private function proxy(bool $fail = false): ContentProxy
    {
        $http = $this->createMock(PublicHttpClient::class);
        if ($fail) { $http->method('get')->willThrowException(new \RuntimeException('HTTP 403')); }
        else { $http->method('get')->willReturn(['body' => '{"author_name":"lauren","html":"<blockquote><p>Public text</p></blockquote>"}']); }
        $graby = $this->getMockBuilder(Graby::class)->setMethods(['fetchContent'])->getMock();
        $graby->expects(self::never())->method('fetchContent');
        return new ContentProxy($graby, $this->createMock(RuleBasedTagger::class), $this->createMock(RuleBasedIgnoreOriginProcessor::class), Validation::createValidator(), new NullLogger(), 'Error', false, new XPostExtractor($http));
    }

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
        $entry = new Entry(new User()); $entry->setContent('Saved snapshot');
        $this->proxy(true)->updateEntry($entry, 'https://x.com/poteto/status/2069824386283319343');
        self::assertSame('Saved snapshot', $entry->getContent());
        self::assertSame(502, $entry->getHttpStatus());
    }
}
