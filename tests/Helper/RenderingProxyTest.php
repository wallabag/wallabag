<?php

declare(strict_types=1);

namespace Tests\Wallabag\Helper;

use Graby\Graby;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\RecursiveValidator;
use Wallabag\Entity\Config;
use Wallabag\Entity\Entry;
use Wallabag\Entity\RenderingProxyHost;
use Wallabag\Entity\User;
use Wallabag\Helper\ContentProxy;
use Wallabag\Helper\RenderingProxy;
use Wallabag\Helper\RuleBasedIgnoreOriginProcessor;
use Wallabag\Helper\RuleBasedTagger;
use Wallabag\HttpClient\Authenticator;
use Wallabag\HttpClient\WallabagClient;

class RenderingProxyTest extends TestCase
{
    public function test_proxy_is_not_used_when_not_configured(): void
    {
        $renderingProxy = new RenderingProxy(null, 0, 100);
        $user = new User();
        $config = new Config($user);

        [$url, $cb] = $renderingProxy->considerUrl($config, 'http://test/');

        $this->assertEquals('http://test/', $url);
        $this->assertNull($cb);
    }

    public function test_proxy_is_not_used_when_configured_but_no_hosts_to_handle(): void
    {
        $renderingProxy = new RenderingProxy('http://proxy/%u', 0, 100);
        $user = new User();
        $config = new Config($user);

        [$url, $cb] = $renderingProxy->considerUrl($config, 'http://test/');

        $this->assertEquals('http://test/', $url);
        $this->assertNull($cb);
    }

    public function test_proxy_is_not_used_when_configured_but_given_host_is_not_in_list(): void
    {
        $renderingProxy = new RenderingProxy('http://proxy/%u', 0, 100);
        $user = new User();
        $config = new Config($user);
        $host = new RenderingProxyHost($user);
        $host->setHost('test');
        $host->setConfig($config);
        $config->addRenderingProxyHost($host);

        [$url, $cb] = $renderingProxy->considerUrl($config, 'http://test2/');

        $this->assertEquals('http://test2/', $url);
        $this->assertNull($cb);
    }

    public function test_proxy_is_used_when_configured_to_handle_all_hosts(): void
    {
        $renderingProxy = new RenderingProxy('http://proxy/%u', 1, 100);
        $user = new User();
        $config = new Config($user);

        [$url, $cb] = $renderingProxy->considerUrl($config, 'http://test/');

        $this->assertEquals('http://proxy/http://test/', $url);
        $this->assertIsCallable($cb);
    }

    public function test_proxy_is_used_when_configured_to_handle_given_host(): void
    {
        $renderingProxy = new RenderingProxy('http://proxy/%u', 0, 100);
        $user = new User();
        $config = new Config($user);
        $host = new RenderingProxyHost($user);
        $host->setHost('test');
        $host->setConfig($config);
        $config->addRenderingProxyHost($host);

        [$url] = $renderingProxy->considerUrl($config, 'http://test/');

        $this->assertEquals('http://proxy/http://test/', $url);
    }

    public function test_proxy_is_used_when_configured_to_handle_parent_of_given_host(): void
    {
        $renderingProxy = new RenderingProxy('http://proxy/%u', 0, 100);
        $user = new User();
        $config = new Config($user);
        $host = new RenderingProxyHost();
        $host->setHost('test');
        $host->setConfig($config);
        $config->addRenderingProxyHost($host);

        [$url] = $renderingProxy->considerUrl($config, 'http://subhost.test/');

        $this->assertEquals('http://proxy/http://subhost.test/', $url);
    }

    public function test_proxy_url_is_recognized(): void
    {
        $renderingProxy = new RenderingProxy('http://proxy/%u', 0, 100);

        $res = $renderingProxy->ownsUrl('http://proxy/http://test/');

        $this->assertTrue($res);
    }

    public function test_non_proxy_url_is_recognized(): void
    {
        $renderingProxy = new RenderingProxy('http://proxy/%u', 0, 100);

        $res = $renderingProxy->ownsUrl('http://anotherhost/http://test/');

        $this->assertFalse($res);
    }

    public function test_timeout_is_not_changed_when_rendering_proxy_is_not_used(): void
    {
        $renderingProxy = new RenderingProxy('', 0, 100);

        $mockResponse = new MockResponse('', ['http_code' => 200]);
        $httpClient = new MockHttpClient($mockResponse);
        $browser = $this->getMockBuilder(HttpBrowser::class)->getMock();
        $authenticator = $this->getMockBuilder(Authenticator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = new WallabagClient(0, $browser, $authenticator, new NullLogger(), $renderingProxy, $httpClient);

        $client->request('GET', 'http://test', [ "timeout" => 10 ]);

        $this->assertEquals(10, $mockResponse->getRequestOptions()['timeout']);
    }

    public function test_timeout_is_changed_when_rendering_proxy_is_used(): void
    {
        $renderingProxy = new RenderingProxy('http://proxy/%u', 0, 100);

        $mockResponse = new MockResponse('', ['http_code' => 200]);
        $httpClient = new MockHttpClient($mockResponse);
        $browser = $this->getMockBuilder(HttpBrowser::class)->getMock();
        $authenticator = $this->getMockBuilder(Authenticator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $client = new WallabagClient(0, $browser, $authenticator, new NullLogger(), $renderingProxy, $httpClient);

        $client->request('GET', 'http://proxy/http://test', [ "timeout" => 10 ]);

        $this->assertEquals(100, $mockResponse->getRequestOptions()['timeout']);
    }

    public function test_proxy_url_is_removed()
    {
        $tagger = $this->getMockBuilder(RuleBasedTagger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ruleBasedIgnoreOriginProcessor =  $this->getMockBuilder(RuleBasedIgnoreOriginProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $validator = $this->getMockBuilder(RecursiveValidator::class)
            ->onlyMethods(['validate'])
            ->disableOriginalConstructor()
            ->getMock();
        $validator->expects($this->any())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $graby = $this->getMockBuilder(Graby::class)
            ->onlyMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => "",
                'title' => 'this is my title',
                'url' => 'http://proxy/http://test',
                'language' => 'fr',
                'status' => '200',
                'description' => 'OG desc',
                'image' => 'http://3.3.3.3/cover.jpg',
                'headers' => [
                    'content-type' => 'text/html',
                ],
            ]);

        $rendering_proxy = new RenderingProxy('http://proxy/%u', 1, 100);

        $proxy = new ContentProxy($graby, $tagger, $ruleBasedIgnoreOriginProcessor, $validator, new NullLogger(), $rendering_proxy, '');
        $entry = new Entry(new User());
        $proxy->updateEntry($entry, 'http://test');

        $this->assertSame('http://test', $entry->getUrl());
    }

    public function test_body_is_post_processed()
    {
        $tagger = $this->getMockBuilder(RuleBasedTagger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $ruleBasedIgnoreOriginProcessor =  $this->getMockBuilder(RuleBasedIgnoreOriginProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();

        $validator = $this->getMockBuilder(RecursiveValidator::class)
            ->onlyMethods(['validate'])
            ->disableOriginalConstructor()
            ->getMock();
        $validator->expects($this->any())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $graby = $this->getMockBuilder(Graby::class)
            ->onlyMethods(['fetchContent'])
            ->disableOriginalConstructor()
            ->getMock();

        $graby->expects($this->any())
            ->method('fetchContent')
            ->willReturn([
                'html' => "&lt;img some-content &gt;",
                'title' => 'this is my title',
                'url' => 'http://proxy/http://test',
                'language' => 'fr',
                'status' => '200',
                'description' => 'OG desc',
                'image' => 'http://3.3.3.3/cover.jpg',
                'headers' => [
                    'content-type' => 'text/html',
                ],
            ]);

        $rendering_proxy = new RenderingProxy('http://proxy/%u', 1, 100);

        $proxy = new ContentProxy($graby, $tagger, $ruleBasedIgnoreOriginProcessor, $validator, new NullLogger(), $rendering_proxy, '');
        $entry = new Entry(new User());
        $proxy->updateEntry($entry, 'http://test');

        $this->assertEquals('<img some-content >', $entry->getContent());
    }
}
