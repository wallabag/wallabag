<?php

namespace Wallabag\CoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WallabagRestControllerTest extends WebTestCase
{
    public function testEmptyGetEntries() {
        $client = $this->createClient();
        $client->request('GET', '/api/entries');
        $this->assertTrue($client->getResponse()->isOk());

        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
        $this->assertEquals('[]', $client->getResponse()->getContent());
    }
}