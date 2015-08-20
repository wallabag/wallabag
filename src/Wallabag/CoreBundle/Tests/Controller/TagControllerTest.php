<?php

namespace Wallabag\CoreBundle\Tests\Controller;

use Wallabag\CoreBundle\Tests\WallabagCoreTestCase;

class TagControllerTest extends WallabagCoreTestCase
{
    public function testList()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/tag/list');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
