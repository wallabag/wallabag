<?php

namespace Wallabag\Tests\Functional\Controller;

use Wallabag\Tests\Functional\WallabagTestCase;

class StaticControllerTest extends WallabagTestCase
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
