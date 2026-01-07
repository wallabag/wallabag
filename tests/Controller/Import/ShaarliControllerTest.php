<?php

namespace Tests\Wallabag\Controller\Import;

use Craue\ConfigBundle\Util\Config;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\Entry;

class ShaarliControllerTest extends WallabagTestCase
{
    public function testImportShaarli()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/shaarli');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());
    }

    public function testImportShaarliWithRabbitEnabled()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->getContainer()->get(Config::class)->set('import_with_rabbitmq', 1);

        $crawler = $client->request('GET', '/import/shaarli');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());

        $client->getContainer()->get(Config::class)->set('import_with_rabbitmq', 0);
    }

    public function testImportShaarliBadFile()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/shaarli');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $data = [
            'upload_import_file[file]' => '',
        ];

        $client->submit($form, $data);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testImportShaarliWithRedisEnabled()
    {
        $this->checkRedis();
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $client->getContainer()->get(Config::class)->set('import_with_redis', 1);

        $crawler = $client->request('GET', '/import/shaarli');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());

        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../fixtures/Import/shaarli-bookmarks.html', 'Bookmarks');

        $data = [
            'upload_import_file[file]' => $file,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.summary', $body[0]);

        $this->assertNotEmpty($client->getContainer()->get(Client::class)->lpop('wallabag.import.shaarli'));

        $client->getContainer()->get(Config::class)->set('import_with_redis', 0);
    }

    public function testImportWallabagWithShaarliFile()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/shaarli');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../fixtures/Import/shaarli-bookmarks.html', 'Bookmarks');

        $data = [
            'upload_import_file[file]' => $file,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.summary', $body[0]);

        $content = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId(
                'https://www.20minutes.fr/sport/4002755-20220928-tarn-lapins-ravagent-terrain-match-rugby-doit-etre-annule',
                $this->getLoggedInUserId()
            );

        $this->assertInstanceOf(Entry::class, $content);
        $this->assertNotEmpty($content->getMimetype(), 'Mimetype for 20minutes.fr is ok');
        $this->assertNotEmpty($content->getPreviewPicture(), 'Preview picture for 20minutes.fr is ok');
        $this->assertNotEmpty($content->getLanguage(), 'Language for 20minutes.fr is ok');
        $this->assertCount(2, $content->getTags());
    }

    public function testImportWallabagWithEmptyFile()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/shaarli');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../fixtures/Import/test.html', 'test.html');

        $data = [
            'upload_import_file[file]' => $file,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.failed', $body[0]);
    }
}
