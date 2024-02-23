<?php

namespace Tests\Wallabag\Controller;

use Tests\Wallabag\WallabagCoreTestCase;

class StaticControllerTest extends WallabagCoreTestCase
{
    public function testAbout()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->request('GET', '/about');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testHowto()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->request('GET', '/howto');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }
}
