<?php

namespace Tests\Wallabag\Controller\Import;

use Craue\ConfigBundle\Util\Config;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Import\PocketImport;

class PocketControllerTest extends WallabagTestCase
{
    public function testImportPocket()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/pocket');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('button[name=action]')->count());
    }

    public function testImportPocketWithRabbitEnabled()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->getContainer()->get(Config::class)->set('import_with_rabbitmq', 1);

        $crawler = $client->request('GET', '/import/pocket');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('button[name=action]')->count());

        $client->getContainer()->get(Config::class)->set('import_with_rabbitmq', 0);
    }

    public function testImportPocketWithRedisEnabled()
    {
        $this->checkRedis();
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->getContainer()->get(Config::class)->set('import_with_redis', 1);

        $crawler = $client->request('GET', '/import/pocket');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('button[name=action]')->count());

        $client->getContainer()->get(Config::class)->set('import_with_redis', 0);
    }

    public function testImportPocketAuthBadToken()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->request('POST', '/import/pocket/auth');

        $this->assertSame(302, $client->getResponse()->getStatusCode());
    }

    public function testImportPocketAuth()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $pocketImport = $this->getMockBuilder(PocketImport::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pocketImport
            ->expects($this->once())
            ->method('getRequestToken')
            ->willReturn('token');

        static::$kernel->getContainer()->set(PocketImport::class, $pocketImport);

        $client->request('POST', '/import/pocket/auth');

        $this->assertSame(301, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('getpocket.com/auth/authorize', $client->getResponse()->headers->get('location'));
    }

    public function testImportPocketCallbackWithBadToken()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $pocketImport = $this->getMockBuilder(PocketImport::class)
            ->disableOriginalConstructor()
            ->getMock();

        $pocketImport
            ->expects($this->once())
            ->method('authorize')
            ->willReturn(false);

        static::$kernel->getContainer()->set(PocketImport::class, $pocketImport);

        $client->request('GET', '/import/pocket/callback');

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/', $client->getResponse()->headers->get('location'), 'Import is ok, redirect to homepage');
        $this->assertSame('flashes.import.notice.failed', $client->getContainer()->get(SessionInterface::class)->getFlashBag()->peek('notice')[0]);
    }

    public function testImportPocketCallback()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $pocketImport = $this->getMockBuilder(PocketImport::class)
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

        static::$kernel->getContainer()->set(PocketImport::class, $pocketImport);

        $client->request('GET', '/import/pocket/callback');

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('/', $client->getResponse()->headers->get('location'), 'Import is ok, redirect to homepage');
        $this->assertSame('flashes.import.notice.summary', $client->getContainer()->get(SessionInterface::class)->getFlashBag()->peek('notice')[0]);
    }
}
