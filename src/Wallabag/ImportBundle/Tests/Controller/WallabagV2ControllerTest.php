<?php

namespace Wallabag\ImportBundle\Tests\Controller;

use Wallabag\CoreBundle\Tests\WallabagCoreTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class WallabagV2ControllerTest extends WallabagCoreTestCase
{
    public function testImportWallabag()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/wallabag-v2');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertEquals(1, $crawler->filter('input[type=file]')->count());
    }

    public function testImportWallabagWithFile()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/wallabag-v2');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__.'/../fixtures/wallabag-v2.json', 'wallabag-v2.json');

        $data = array(
            'upload_import_file[file]' => $file,
        );

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(array('_text')));
        $this->assertContains('Import summary', $body[0]);

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId(
                'http://www.liberation.fr/planete/2015/10/26/refugies-l-ue-va-creer-100-000-places-d-accueil-dans-les-balkans_1408867',
                $this->getLoggedInUserId()
            );

        $this->assertEmpty($content->getMimetype());
        $this->assertEmpty($content->getPreviewPicture());
        $this->assertEmpty($content->getLanguage());

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId(
                'https://www.mediapart.fr/',
                $this->getLoggedInUserId()
            );

        $this->assertNotEmpty($content->getMimetype());
        $this->assertNotEmpty($content->getPreviewPicture());
        $this->assertNotEmpty($content->getLanguage());
    }

    public function testImportWallabagWithEmptyFile()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/wallabag-v2');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__.'/../fixtures/test.txt', 'test.txt');

        $data = array(
            'upload_import_file[file]' => $file,
        );

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(array('_text')));
        $this->assertContains('Import failed, please try again', $body[0]);
    }
}
