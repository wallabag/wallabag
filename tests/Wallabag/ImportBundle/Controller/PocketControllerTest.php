<?php

namespace Tests\Wallabag\ImportBundle\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;

class PocketControllerTest extends WallabagCoreTestCase
{
    public function testImportPocket()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/pocket');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('button[type=submit]')->count());
    }

    public function testImportPocketAuthBadToken()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/pocket/auth');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testImportPocketAuth()
    {
        $this->markTestSkipped('PocketImport: Find a way to properly mock a service.');

        $this->logInAs('admin');
        $client = $this->getClient();

        $pocketImport = $this->getMockBuilder('Wallabag\ImportBundle\Import\PocketImport')
            ->disableOriginalConstructor()
            ->getMock();

        $pocketImport
            ->expects($this->once())
            ->method('getRequestToken')
            ->willReturn('token');

        $client->getContainer()->set('wallabag_import.pocket.import', $pocketImport);

        $crawler = $client->request('GET', '/import/pocket/auth');

        $this->assertEquals(301, $client->getResponse()->getStatusCode());
        $this->assertContains('getpocket.com/auth/authorize', $client->getResponse()->headers->get('location'));
    }

    public function testImportPocketCallbackWithBadToken()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/pocket/callback');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertContains('import/pocket', $client->getResponse()->headers->get('location'));
        $this->assertEquals('flashes.import.notice.failed', $client->getContainer()->get('session')->getFlashBag()->peek('notice')[0]);
    }
}
