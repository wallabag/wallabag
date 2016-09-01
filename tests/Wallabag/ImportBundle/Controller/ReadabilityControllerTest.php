<?php

namespace Tests\Wallabag\ImportBundle\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ReadabilityControllerTest extends WallabagCoreTestCase
{
    public function testImportReadability()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/readability');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertEquals(1, $crawler->filter('input[type=file]')->count());
    }

    public function testImportReadabilityWithFile()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/readability');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__.'/../fixtures/readability.json', 'readability.json');

        $data = [
            'upload_import_file[file]' => $file,
        ];

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId(
                'https://venngage.com/blog/hashtags-are-worthless/',
                $this->getLoggedInUserId()
            );

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertContains('flashes.import.notice.summary', $body[0]);
    }

    public function testImportReadabilityWithFileAndMarkAllAsRead()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/readability');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__.'/../fixtures/readability-read.json', 'readability-read.json');

        $data = [
            'upload_import_file[file]' => $file,
            'upload_import_file[mark_as_read]' => 1,
        ];

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $content1 = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId(
                'https://blog.travis-ci.com/2016-07-28-what-we-learned-from-analyzing-2-million-travis-builds/',
                $this->getLoggedInUserId()
            );

        $this->assertTrue($content1->isArchived());

        $content2 = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId(
                'https://facebook.github.io/graphql/',
                $this->getLoggedInUserId()
            );

        $this->assertTrue($content2->isArchived());

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertContains('flashes.import.notice.summary', $body[0]);
    }

    public function testImportReadabilityWithEmptyFile()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/readability');
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
