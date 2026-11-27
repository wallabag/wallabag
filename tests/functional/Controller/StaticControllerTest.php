<?php

namespace Wallabag\Tests\Functional\Controller;

use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Wallabag\Helper\UpdateChecker;
use Wallabag\Tests\Functional\WallabagTestCase;

class StaticControllerTest extends WallabagTestCase
{
    public function testAbout(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->request('GET', '/about');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testAboutShowsTheAvailableUpdateToAdminsOnly(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $this->mockUpdateChecker();

        $crawler = $client->request('GET', '/about');
        $this->assertStringContainsString('update.available', $crawler->filter('#set1')->text());
        $this->assertCount(1, $crawler->filter('.card-panel.red'));

        $this->logInAs('bob');
        $crawler = $client->request('GET', '/about');
        $this->assertStringNotContainsString('update.available', $crawler->filter('#set1')->text());
        $this->assertCount(0, $crawler->filter('.card-panel.red'));
    }

    public function testHowto(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->request('GET', '/howto');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testQuickstartHidesDisabledImporters(): void
    {
        $client = $this->getClientWithReducedImporters();
        $this->logInAs('admin');

        $crawler = $client->request('GET', '/quickstart');

        self::assertResponseIsSuccessful();
        $this->assertCount(0, $crawler->filter('a[href="/import/pocket_csv"]'));
        $this->assertCount(1, $crawler->filter('a[href="/import/readability"]'));
    }

    private function mockUpdateChecker(): void
    {
        $releases = [['version' => '99.0.0', 'prerelease' => false, 'security' => true, 'php_min' => '8.2', 'notes_url' => 'https://wallabag.org/news/99', 'release_url' => 'https://example.org/99']];
        $client = new MockHttpClient(new MockResponse(json_encode(['releases' => $releases])));

        $this->getTestClient()->getContainer()->set(UpdateChecker::class, new UpdateChecker($client, new ArrayAdapter(), new NullLogger(), '2.7.0', true));
    }
}
