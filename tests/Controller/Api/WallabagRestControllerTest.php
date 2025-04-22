<?php

namespace Tests\Wallabag\Controller\Api;

use Craue\ConfigBundle\Util\Config;

class WallabagRestControllerTest extends WallabagApiTestCase
{
    public function testGetVersion()
    {
        // create a new client instead of using $this->client to be sure client isn't authenticated
        $client = $this->createUnauthorizedClient();
        $client->request('GET', '/api/version');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame($client->getContainer()->getParameter('wallabag.version'), $content);
    }

    public function testGetInfo()
    {
        // create a new client instead of using $this->client to be sure client isn't authenticated
        $client = $this->createUnauthorizedClient();
        $client->request('GET', '/api/info');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('appname', $content);
        $this->assertArrayHasKey('version', $content);
        $this->assertArrayHasKey('allowed_registration', $content);

        $this->assertSame('wallabag', $content['appname']);
    }

    public function testAllowedRegistration()
    {
        // create a new client instead of using $this->client to be sure client isn't authenticated
        $client = $this->createUnauthorizedClient();

        if (!$client->getContainer()->getParameter('fosuser_registration')) {
            $this->markTestSkipped('fosuser_registration is not enabled.');
        }

        $client->getContainer()->get(Config::class)->set('api_user_registration', '1');

        $client->request('GET', '/api/info');

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertTrue($content['allowed_registration']);

        $client->getContainer()->get(Config::class)->set('api_user_registration', '0');

        $client->request('GET', '/api/info');

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertFalse($content['allowed_registration']);
    }
}
