<?php

namespace Tests\Wallabag\ImportBundle\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ChromeControllerTest extends WallabagCoreTestCase
{
    public function testImportChrome()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/chrome');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertEquals(1, $crawler->filter('input[type=file]')->count());
    }

    public function testImportChromeWithRabbitEnabled()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->getContainer()->get('craue_config')->set('import_with_rabbitmq', 1);

        $crawler = $client->request('GET', '/import/chrome');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertEquals(1, $crawler->filter('input[type=file]')->count());

        $client->getContainer()->get('craue_config')->set('import_with_rabbitmq', 0);
    }

    public function testImportChromeBadFile()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/chrome');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $data = [
            'upload_import_file[file]' => '',
        ];

        $client->submit($form, $data);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testImportChromeWithRedisEnabled()
    {
        $this->logInAs('admin');
        $client = $this->getClient();
        $client->getContainer()->get('craue_config')->set('import_with_redis', 1);

        $crawler = $client->request('GET', '/import/chrome');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertEquals(1, $crawler->filter('input[type=file]')->count());

        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__.'/../fixtures/chrome-bookmarks', 'Bookmarks');

        $data = [
            'upload_import_file[file]' => $file,
        ];

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertContains('flashes.import.notice.summary', $body[0]);

        $this->assertNotEmpty($client->getContainer()->get('wallabag_core.redis.client')->lpop('wallabag.import.chrome'));

        $client->getContainer()->get('craue_config')->set('import_with_redis', 0);
    }

    public function testImportWallabagWithChromeFile()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/chrome');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__.'/../fixtures/chrome-bookmarks', 'Bookmarks');

        $data = [
            'upload_import_file[file]' => $file,
        ];

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertContains('flashes.import.notice.summary', $body[0]);

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId(
                'http://www.usinenouvelle.com/article/la-multiplication-des-chefs-de-projet-est-une-catastrophe-manageriale-majeure-affirme-le-sociologue-francois-dupuy.N307730',
                $this->getLoggedInUserId()
            );

        $this->assertNotEmpty($content->getPreviewPicture());
        $this->assertNotEmpty($content->getLanguage());
        $this->assertEquals(0, count($content->getTags()));
    }

    public function testImportWallabagWithEmptyFile()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/chrome');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__.'/../fixtures/test.txt', 'test.txt');

        $data = [
            'upload_import_file[file]' => $file,
        ];

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertContains('flashes.import.notice.failed', $body[0]);
    }
}
