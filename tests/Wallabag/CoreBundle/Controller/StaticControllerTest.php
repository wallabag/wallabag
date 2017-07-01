<?php

namespace Tests\Wallabag\CoreBundle\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;

class StaticControllerTest extends WallabagCoreTestCase
{
    public function testAbout()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/about');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testHowto()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/howto');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }
}
