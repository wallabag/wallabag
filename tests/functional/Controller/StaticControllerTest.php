<?php

namespace Wallabag\Tests\Functional\Controller;

use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Wallabag\Entity\Entry;
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

    public function testOpenSearchDescription(): void
    {
        $client = $this->getTestClient();

        $client->request('GET', '/opensearch.xml');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('application/opensearchdescription+xml', $client->getResponse()->headers->get('Content-Type'));

        $xpath = $this->getOpenSearchXPath($client->getResponse()->getContent());

        $this->assertSame('wallabag', $xpath->query('/os:OpenSearchDescription/os:ShortName')->item(0)->nodeValue);
        $this->assertStringEndsWith('/favicon.ico', $xpath->query('/os:OpenSearchDescription/os:Image')->item(0)->nodeValue);
        $this->assertSame(
            'http://localhost/search?search_entry%5Bterm%5D={searchTerms}',
            $xpath->query('/os:OpenSearchDescription/os:Url[@type="text/html"]/@template')->item(0)->nodeValue,
        );
        $this->assertSame(
            'http://localhost/opensearch.xml',
            $xpath->query('/os:OpenSearchDescription/os:Url[@rel="self"]/@template')->item(0)->nodeValue,
        );
    }

    public function testOpenSearchDescriptionIsLinkedFromPages(): void
    {
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/login');

        $this->assertSame('/opensearch.xml', $crawler->filter('head link[rel="search"][type="application/opensearchdescription+xml"]')->attr('href'));

        $this->logInAs('admin');

        $crawler = $client->request('GET', '/unread/list');

        $this->assertSame('/opensearch.xml', $crawler->filter('head link[rel="search"][type="application/opensearchdescription+xml"]')->attr('href'));
    }

    public function testOpenSearchUrlTemplateSearchesEntries(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('https://www.example.org/opensearch');
        $entry->setTitle('OpenSearch needle & haystack');
        $this->getEntityManager()->persist($entry);
        $this->getEntityManager()->flush();

        $client->request('GET', '/opensearch.xml');

        $template = $this->getOpenSearchXPath($client->getResponse()->getContent())
            ->query('/os:OpenSearchDescription/os:Url[@type="text/html"]/@template')
            ->item(0)
            ->nodeValue;

        $crawler = $client->request('GET', str_replace('{searchTerms}', rawurlencode('needle & haystack'), $template));

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('[data-test="entry"]'));
        $this->assertSame((string) $entry->getId(), $crawler->filter('[data-test="entry"]')->attr('data-entry-id'));
    }

    private function getOpenSearchXPath(string $content): \DOMXPath
    {
        $doc = new \DOMDocument();
        $doc->loadXML($content);

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('os', 'http://a9.com/-/spec/opensearch/1.1/');

        return $xpath;
    }

    private function mockUpdateChecker(): void
    {
        $releases = [['version' => '99.0.0', 'prerelease' => false, 'security' => true, 'php_min' => '8.2', 'notes_url' => 'https://wallabag.org/news/99', 'release_url' => 'https://example.org/99']];
        $client = new MockHttpClient(new MockResponse(json_encode(['releases' => $releases])));

        $this->getTestClient()->getContainer()->set(UpdateChecker::class, new UpdateChecker($client, new ArrayAdapter(), new NullLogger(), '2.7.0', true));
    }
}
