<?php

namespace Tests\Wallabag\Controller\Api;

class SearchRestControllerTest extends WallabagApiTestCase
{
    public function testGetSearchWithFullOptions()
    {
        $this->client->request('GET', '/api/search', [
            'page' => 1,
            'perPage' => 2,
            'term' => 'entry', // 6 results
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, \count($content));
        $this->assertArrayHasKey('items', $content['_embedded']);
        $this->assertGreaterThanOrEqual(0, $content['total']);
        $this->assertSame(1, $content['page']);
        $this->assertSame(2, $content['limit']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertArrayHasKey('_links', $content);
        $this->assertArrayHasKey('self', $content['_links']);
        $this->assertArrayHasKey('first', $content['_links']);
        $this->assertArrayHasKey('last', $content['_links']);

        foreach (['self', 'first', 'last'] as $link) {
            $this->assertArrayHasKey('href', $content['_links'][$link]);
            $this->assertStringContainsString('term=entry', $content['_links'][$link]['href']);
        }

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetSearchWithNoLimit()
    {
        $this->client->request('GET', '/api/search', [
            'term' => 'entry',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, \count($content));
        $this->assertArrayHasKey('items', $content['_embedded']);
        $this->assertGreaterThanOrEqual(0, $content['total']);
        $this->assertSame(1, $content['page']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertArrayHasKey('_links', $content);
        $this->assertArrayHasKey('self', $content['_links']);
        $this->assertArrayHasKey('first', $content['_links']);
        $this->assertArrayHasKey('last', $content['_links']);

        foreach (['self', 'first', 'last'] as $link) {
            $this->assertArrayHasKey('href', $content['_links'][$link]);
            $this->assertStringContainsString('term=entry', $content['_links'][$link]['href']);
        }

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }
}
