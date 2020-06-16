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

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('button[name=action]')->count());
    }

    public function testImportPocketWithRabbitEnabled()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->getContainer()->get('craue_config')->set('import_with_rabbitmq', 1);

        $crawler = $client->request('GET', '/import/pocket');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('button[name=action]')->count());

        $client->getContainer()->get('craue_config')->set('import_with_rabbitmq', 0);
    }

    public function testImportPocketWithRedisEnabled()
    {
        $this->checkRedis();
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->getContainer()->get('craue_config')->set('import_with_redis', 1);

        $crawler = $client->request('GET', '/import/pocket');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('button[name=action]')->count());

        $client->getContainer()->get('craue_config')->set('import_with_redis', 0);
    }

    public function testImportPocketAuthBadToken()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/import/pocket/auth');

        $this->assertSame(302, $client->getResponse()->getStatusCode());
    }

    public function testImportPocketAuth()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $pocketImport = $this->getMockBuilder('Wallabag\ImportBundle\Import\PocketImport')
            ->disableOriginalConstructor()
            ->getMock();

        $pocketImport
            ->expects($this->once())
            ->method('getRequestToken')
            ->willReturn('token');

        static::$kernel->getContainer()->set('wallabag_import.pocket.import', $pocketImport);

        $client->request('GET', '/import/pocket/auth');

        $this->assertSame(301, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('getpocket.com/auth/authorize', $client->getResponse()->headers->get('location'));
    }

    public function testImportPocketCallbackWithBadToken()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $pocketImport = $this->getMockBuilder('Wallabag\ImportBundle\Import\PocketImport')
            ->disableOriginalConstructor()
            ->getMock();

        $pocketImport
            ->expects($this->once())
            ->method('authorize')
            ->willReturn(false);

        static::$kernel->getContainer()->set('wallabag_import.pocket.import', $pocketImport);

        $client->request('GET', '/import/pocket/callback');

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/', $client->getResponse()->headers->get('location'), 'Import is ok, redirect to homepage');
        $this->assertSame('flashes.import.notice.failed', $client->getContainer()->get('session')->getFlashBag()->peek('notice')[0]);
    }

    public function testImportPocketCallback()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $pocketImport = $this->getMockBuilder('Wallabag\ImportBundle\Import\PocketImport')
            ->disableOriginalConstructor()
            ->getMock();

        $pocketImport
            ->expects($this->once())
            ->method('authorize')
            ->willReturn(true);

        $pocketImport
            ->expects($this->once())
            ->method('setMarkAsRead')
            ->with(false)
            ->willReturn($pocketImport);

        $pocketImport
            ->expects($this->once())
            ->method('import')
            ->willReturn(true);

        static::$kernel->getContainer()->set('wallabag_import.pocket.import', $pocketImport);

        $client->request('GET', '/import/pocket/callback');

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/', $client->getResponse()->headers->get('location'), 'Import is ok, redirect to homepage');
        $this->assertSame('flashes.import.notice.summary', $client->getContainer()->get('session')->getFlashBag()->peek('notice')[0]);
    }
}
