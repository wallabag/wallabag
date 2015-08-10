<?php

namespace Wallabag\CoreBundle\Tests\Controller;

use Wallabag\CoreBundle\Tests\WallabagCoreTestCase;
use Doctrine\ORM\AbstractQuery;

class StaticControllerTest extends WallabagCoreTestCase
{
    public function testAbout()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/about');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testHowto()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/howto');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
