<?php

namespace Wallabag\CoreBundle\Tests\Controller;

use Wallabag\CoreBundle\Tests\WallabagCoreTestCase;

class ExportControllerTest extends WallabagCoreTestCase
{
    public function testLogin()
    {
        $client = $this->getClient();

        $client->request('GET', '/export/unread.csv');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertContains('login', $client->getResponse()->headers->get('location'));
    }

    public function testUnknownCategoryExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/export/awesomeness.epub');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testUnknownFormatExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/export/unread.xslx');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testEpubExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        ob_start();
        $crawler = $client->request('GET', '/export/archive.epub');
        ob_end_clean();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertEquals('application/epub+zip', $headers->get('content-type'));
        $this->assertEquals('attachment; filename="Archive articles.epub"', $headers->get('content-disposition'));
        $this->assertEquals('binary', $headers->get('content-transfer-encoding'));
    }

    public function testMobiExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUsernameAndNotArchived('admin');

        ob_start();
        $crawler = $client->request('GET', '/export/'.$content->getId().'.mobi');
        ob_end_clean();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertEquals('application/x-mobipocket-ebook', $headers->get('content-type'));
        $this->assertEquals('attachment; filename="'.preg_replace('/[^A-Za-z0-9\-]/', '', $content->getTitle()).'.mobi"', $headers->get('content-disposition'));
        $this->assertEquals('binary', $headers->get('content-transfer-encoding'));
    }

    public function testPdfExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        ob_start();
        $crawler = $client->request('GET', '/export/all.pdf');
        ob_end_clean();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertEquals('application/pdf', $headers->get('content-type'));
        $this->assertEquals('attachment; filename="All articles.pdf"', $headers->get('content-disposition'));
        $this->assertEquals('binary', $headers->get('content-transfer-encoding'));
    }

    public function testCsvExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        ob_start();
        $crawler = $client->request('GET', '/export/unread.csv');
        ob_end_clean();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertEquals('application/csv', $headers->get('content-type'));
        $this->assertEquals('attachment; filename="Unread articles.csv"', $headers->get('content-disposition'));
        $this->assertEquals('UTF-8', $headers->get('content-transfer-encoding'));

        $csv = str_getcsv($client->getResponse()->getContent(), "\n");

        $this->assertGreaterThan(1, $csv);
        $this->assertEquals('Title;URL;Content;Tags;"MIME Type";Language', $csv[0]);
    }

    public function testJsonExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        ob_start();
        $crawler = $client->request('GET', '/export/all.json');
        ob_end_clean();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertEquals('application/json', $headers->get('content-type'));
        $this->assertEquals('attachment; filename="All articles.json"', $headers->get('content-disposition'));
        $this->assertEquals('UTF-8', $headers->get('content-transfer-encoding'));
    }

    public function testXmlExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        ob_start();
        $crawler = $client->request('GET', '/export/unread.xml');
        ob_end_clean();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertEquals('application/xml', $headers->get('content-type'));
        $this->assertEquals('attachment; filename="Unread articles.xml"', $headers->get('content-disposition'));
        $this->assertEquals('UTF-8', $headers->get('content-transfer-encoding'));
    }
}
