<?php

namespace Wallabag\ImportBundle\Tests\Controller;

use Wallabag\CoreBundle\Tests\WallabagCoreTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class WallabagV1ControllerTest extends WallabagCoreTestCase
{
    public function testImportWallabag()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/wallabag-v1');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertEquals(1, $crawler->filter('input[type=file]')->count());
    }

    public function testImportWallabagWithFile()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/wallabag-v1');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__.'/../fixtures/wallabag-v1.json', 'wallabag-v1.json');

        $data = array(
            'upload_import_file[file]' => $file,
        );

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId(
                'http://www.framablog.org/index.php/post/2014/02/05/Framabag-service-libre-gratuit-interview-developpeur',
                $this->getLoggedInUserId()
            );

        $tag = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Tag')
            ->findOneByLabel('Framabag');

        $this->assertTrue($content->getTags()->contains($tag));

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(array('_text')));
        $this->assertContains('flashes.import.notice.summary', $body[0]);
    }

    public function testImportWallabagWithFileAndMarkAllAsRead()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/wallabag-v1');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__.'/../fixtures/wallabag-v1-read.json', 'wallabag-v1-read.json');

        $data = array(
            'upload_import_file[file]' => $file,
            'upload_import_file[mark_as_read]' => 1,
        );

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $content1 = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId(
                'http://gilbert.pellegrom.me/recreating-the-square-slider',
                $this->getLoggedInUserId()
            );

        $this->assertTrue($content1->isArchived());

        $content2 = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId(
                'https://www.wallabag.org/features/',
                $this->getLoggedInUserId()
            );

        $this->assertTrue($content2->isArchived());

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(array('_text')));
        $this->assertContains('flashes.import.notice.summary', $body[0]);
    }

    public function testImportWallabagWithEmptyFile()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/wallabag-v1');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__.'/../fixtures/test.txt', 'test.txt');

        $data = array(
            'upload_import_file[file]' => $file,
        );

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(array('_text')));
        $this->assertContains('flashes.import.notice.failed', $body[0]);
    }
}
