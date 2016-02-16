<?php
/**
 * Created by PhpStorm.
 * User: nicosomb
 * Date: 16/02/16
 * Time: 19:39.
 */
namespace Wallabag\CoreBundle\Tests\Controller;

use Wallabag\CoreBundle\Tests\WallabagCoreTestCase;

class DeveloperControllerTest extends WallabagCoreTestCase
{
    public function testNewClient()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/developer/client/create');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[type=submit]')->form();

        $crawler = $client->submit($form);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Make sure to copy these parameters now.', $client->getResponse()->getContent());
    }
}
