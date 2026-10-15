<?php

namespace Wallabag\Tests\Functional\Controller;

use Wallabag\Tests\Functional\WallabagTestCase;

class GrabberControllerTest extends WallabagTestCase
{
    public function testAuthenticationAndCsrf(): void
    {
        $client = $this->getTestClient();
        $client->request('GET', '/grabbers');
        self::assertSame(302, $client->getResponse()->getStatusCode());
        $client->request('GET', '/api/grabbers.json');
        self::assertContains($client->getResponse()->getStatusCode(), [401, 403]);
        $this->logInAs('admin');
        $client->request('GET', '/grabbers');
        self::assertSame(200, $client->getResponse()->getStatusCode());
        self::assertStringContainsString('Medium public stories', $client->getResponse()->getContent());
        $client->request('POST', '/grabbers', ['id' => 'medium', 'token' => 'invalid']);
        self::assertSame(403, $client->getResponse()->getStatusCode());
    }
}
