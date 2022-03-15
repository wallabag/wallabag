<?php

namespace Tests\Wallabag\ApiBundle\Controller;

use Tests\Wallabag\ApiBundle\WallabagApiTestCase;

class ConfigRestControllerTest extends WallabagApiTestCase
{
    public function testGetConfig()
    {
        $this->client->request('GET', '/api/config.json');
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $content);
        $this->assertArrayHasKey('theme', $content);
        $this->assertArrayHasKey('items_per_page', $content);
        $this->assertArrayHasKey('language', $content);
        $this->assertArrayHasKey('reading_speed', $content);
        $this->assertArrayHasKey('pocket_consumer_key', $content);
        $this->assertArrayHasKey('action_mark_as_read', $content);
        $this->assertArrayHasKey('list_mode', $content);

        $this->assertSame('material', $content['theme']);
        $this->assertSame(200.0, $content['reading_speed']);
        $this->assertSame('xxxxx', $content['pocket_consumer_key']);

        $this->assertSame('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testGetConfigWithoutAuthentication()
    {
        $client = static::createClient();
        $client->request('GET', '/api/config.json');
        $this->assertSame(401, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $content);
        $this->assertArrayHasKey('error_description', $content);

        $this->assertSame('access_denied', $content['error']);

        $this->assertSame('application/json', $client->getResponse()->headers->get('Content-Type'));
    }
}
