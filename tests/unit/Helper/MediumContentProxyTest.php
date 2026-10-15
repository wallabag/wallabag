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
use Wallabag\Helper\MediumExtractor;
use Wallabag\Helper\RuleBasedIgnoreOriginProcessor;
use Wallabag\Helper\RuleBasedTagger;

class MediumContentProxyTest extends TestCase
{
    /** @dataProvider ordinaryUrls */
    public function testUnrelatedAndDisabledMediumUseNormalExtraction(string $url, bool $enabled): void
    {
        $user = new User();
        $config = new Config($user);
        $config->setMediumGrabberEnabled($enabled);
        $user->setConfig($config);
        $entry = new Entry($user);
        $medium = $this->createMock(MediumExtractor::class);
        $medium->expects(self::never())->method('extract');
        $graby = $this->getMockBuilder(Graby::class)->setMethods(['fetchContent'])->getMock();
        $graby->expects(self::once())->method('fetchContent')->with($url)->willReturn(['title' => 'Ordinary extraction', 'html' => '<p>Normal content.</p>', 'url' => $url, 'status' => 200]);
        $this->proxy($graby, $medium)->updateEntry($entry, $url);
        self::assertSame('Ordinary extraction', $entry->getTitle());
        self::assertSame(200, $entry->getHttpStatus());
    }

    public function ordinaryUrls(): array
    {
        return [
            ['https://example.org/ordinary', true],
            ['https://medium.com.evil.example/story-abcdef012345', true],
            ['https://medium.com/p/abcdef012345', false],
        ];
    }

    public function testFailedMediumReloadKeepsExistingBody(): void
    {
        $user = new User();
        $user->setConfig(new Config($user));
        $entry = new Entry($user);
        $entry->setContent('Saved body');
        $medium = $this->createMock(MediumExtractor::class);
        $medium->expects(self::once())->method('extract')->willReturn(['html' => '<p>Unavailable</p>', 'title' => 'Unavailable', 'url' => 'https://medium.com/p/abcdef012345', 'status' => 502]);
        $graby = $this->getMockBuilder(Graby::class)->setMethods(['fetchContent'])->getMock();
        $graby->expects(self::never())->method('fetchContent');
        $this->proxy($graby, $medium)->updateEntry($entry, 'https://medium.com/p/abcdef012345');
        self::assertSame('Saved body', $entry->getContent());
        self::assertSame(502, $entry->getHttpStatus());
    }

    private function proxy(Graby $graby, MediumExtractor $medium): ContentProxy
    {
        return new ContentProxy($graby, $this->createMock(RuleBasedTagger::class), $this->createMock(RuleBasedIgnoreOriginProcessor::class), Validation::createValidator(), new NullLogger(), 'Error', false, null, $medium);
    }
}
