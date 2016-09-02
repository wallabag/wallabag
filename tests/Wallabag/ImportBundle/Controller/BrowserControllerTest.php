<?php

namespace Tests\Wallabag\ImportBundle\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BrowserControllerTest extends WallabagCoreTestCase
{
    public function testImportWallabag()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/browser');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertEquals(1, $crawler->filter('input[type=file]')->count());
    }

    public function testImportWallabagWithFile()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/browser');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__.'/../fixtures/bookmarks.json', 'Bookmarks');

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
                'http://lexpansion.lexpress.fr/high-tech/orange-offre-un-meilleur-reseau-mobile-que-bouygues-et-sfr-free-derriere_1811554.html',
                $this->getLoggedInUserId()
            );

        $this->assertNotEmpty($content->getMimetype());
        $this->assertNotEmpty($content->getPreviewPicture());
        $this->assertNotEmpty($content->getLanguage());
        $this->assertEquals(0, count($content->getTags()));

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId(
                'http://stackoverflow.com/questions/15017163/parser-for-exported-bookmarks-html-file-of-google-chrome-and-mozilla-in-java',
                $this->getLoggedInUserId()
            );

        $this->assertNotEmpty($content->getMimetype());
        $this->assertNotEmpty($content->getPreviewPicture());
        $this->assertEmpty($content->getLanguage());
    }

    public function testImportWallabagWithEmptyFile()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/browser');
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
