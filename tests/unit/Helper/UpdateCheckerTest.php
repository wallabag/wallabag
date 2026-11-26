<?php

namespace Wallabag\Tests\Unit\Helper;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Wallabag\Helper\UpdateChecker;

class UpdateCheckerTest extends TestCase
{
    public function testReturnsTheHighestStableRelease(): void
    {
        $checker = $this->checker([
            ['version' => '2.7.2-beta.1', 'prerelease' => true, 'security' => false, 'php_min' => '8.2', 'notes_url' => null, 'release_url' => 'https://example.org/2.7.2-beta.1'],
            ['version' => '2.7.1', 'prerelease' => false, 'security' => true, 'php_min' => '8.2', 'notes_url' => 'https://wallabag.org/news/2.7.1', 'release_url' => 'https://example.org/2.7.1'],
            ['version' => '2.7.0', 'prerelease' => false, 'security' => false, 'php_min' => '8.2', 'notes_url' => null, 'release_url' => 'https://example.org/2.7.0'],
        ]);

        $this->assertSame(['version' => '2.7.1', 'security' => true, 'url' => 'https://wallabag.org/news/2.7.1'], $checker->getAvailableUpdate());
    }

    public function testReturnsNothingWhenTheReleasesCanNotBeFetched(): void
    {
        $client = new MockHttpClient(new MockResponse('', ['http_code' => 503]));

        $this->assertNull((new UpdateChecker($client, new ArrayAdapter(), new NullLogger(), '2.7.0', true))->getAvailableUpdate());
    }

    private function checker(array $releases): UpdateChecker
    {
        $client = new MockHttpClient(new MockResponse(json_encode(['releases' => $releases])));

        return new UpdateChecker($client, new ArrayAdapter(), new NullLogger(), '2.7.0', true);
    }
}
