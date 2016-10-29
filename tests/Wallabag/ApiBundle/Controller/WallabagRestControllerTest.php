<?php

namespace Tests\Wallabag\ApiBundle\Controller;

use Tests\Wallabag\ApiBundle\WallabagApiTestCase;

class WallabagRestControllerTest extends WallabagApiTestCase
{
    public function testGetVersion()
    {
        $this->client->request('GET', '/api/version');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $content = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals($this->client->getContainer()->getParameter('wallabag_core.version'), $content);
    }
}
