<?php

namespace Tests\Wallabag\Controller\Api;

class ConfigRestControllerTest extends WallabagApiTestCase
{
    public function testGetConfig()
    {
        $this->client->request('GET', '/api/config.json');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $config = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $config);
        $this->assertArrayHasKey('items_per_page', $config);
        $this->assertArrayHasKey('language', $config);
        $this->assertArrayHasKey('reading_speed', $config);
        $this->assertArrayHasKey('action_mark_as_read', $config);
        $this->assertArrayHasKey('list_mode', $config);
        $this->assertArrayHasKey('display_thumbnails', $config);

        $this->assertSame(200.0, $config['reading_speed']);
        $this->assertSame('en', $config['language']);

        $this->assertCount(7, $config);

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetConfigWithoutAuthentication()
    {
        $client = $this->createUnauthorizedClient();
        $client->request('GET', '/api/config.json');
        $this->assertSame(401, $client->getResponse()->getStatusCode());

        $config = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $config);
        $this->assertArrayHasKey('error_description', $config);

        $this->assertSame('access_denied', $config['error']);

        $this->assertSame('application/json', $client->getResponse()->headers->get('Content-Type'));
    }
}
