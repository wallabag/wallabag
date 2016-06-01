<?php

namespace Tests\Wallabag\CoreBundle\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;

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

        $client->request('GET', '/export/awesomeness.epub');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testUnknownFormatExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/export/unread.xslx');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testUnsupportedFormatExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/export/unread.doc');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUsernameAndNotArchived('admin');

        $client->request('GET', '/export/'.$content->getId().'.doc');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testBadEntryId()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/export/0.mobi');

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

    public function testTxtExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        ob_start();
        $crawler = $client->request('GET', '/export/all.txt');
        ob_end_clean();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertEquals('text/plain; charset=UTF-8', $headers->get('content-type'));
        $this->assertEquals('attachment; filename="All articles.txt"', $headers->get('content-disposition'));
        $this->assertEquals('UTF-8', $headers->get('content-transfer-encoding'));
    }

    public function testCsvExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        // to be sure results are the same
        $contentInDB = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->createQueryBuilder('e')
            ->leftJoin('e.user', 'u')
            ->where('u.username = :username')->setParameter('username', 'admin')
            ->andWhere('e.isArchived = true')
            ->getQuery()
            ->getArrayResult();

        ob_start();
        $crawler = $client->request('GET', '/export/archive.csv');
        ob_end_clean();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertEquals('application/csv', $headers->get('content-type'));
        $this->assertEquals('attachment; filename="Archive articles.csv"', $headers->get('content-disposition'));
        $this->assertEquals('UTF-8', $headers->get('content-transfer-encoding'));

        $csv = str_getcsv($client->getResponse()->getContent(), "\n");

        $this->assertGreaterThan(1, $csv);
        // +1 for title line
        $this->assertEquals(count($contentInDB) + 1, count($csv));
        $this->assertEquals('Title;URL;Content;Tags;"MIME Type";Language', $csv[0]);
    }

    public function testJsonExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        // to be sure results are the same
        $contentInDB = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->createQueryBuilder('e')
            ->leftJoin('e.user', 'u')
            ->where('u.username = :username')->setParameter('username', 'admin')
            ->getQuery()
            ->getArrayResult();

        ob_start();
        $crawler = $client->request('GET', '/export/all.json');
        ob_end_clean();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertEquals('application/json', $headers->get('content-type'));
        $this->assertEquals('attachment; filename="All articles.json"', $headers->get('content-disposition'));
        $this->assertEquals('UTF-8', $headers->get('content-transfer-encoding'));

        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(count($contentInDB), count($content));
        $this->assertArrayHasKey('id', $content[0]);
        $this->assertArrayHasKey('title', $content[0]);
        $this->assertArrayHasKey('url', $content[0]);
        $this->assertArrayHasKey('is_archived', $content[0]);
        $this->assertArrayHasKey('is_starred', $content[0]);
        $this->assertArrayHasKey('content', $content[0]);
        $this->assertArrayHasKey('mimetype', $content[0]);
        $this->assertArrayHasKey('language', $content[0]);
        $this->assertArrayHasKey('reading_time', $content[0]);
        $this->assertArrayHasKey('domain_name', $content[0]);
        $this->assertArrayHasKey('tags', $content[0]);
    }

    public function testXmlExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        // to be sure results are the same
        $contentInDB = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->createQueryBuilder('e')
            ->leftJoin('e.user', 'u')
            ->where('u.username = :username')->setParameter('username', 'admin')
            ->andWhere('e.isArchived = false')
            ->getQuery()
            ->getArrayResult();

        ob_start();
        $crawler = $client->request('GET', '/export/unread.xml');
        ob_end_clean();

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertEquals('application/xml', $headers->get('content-type'));
        $this->assertEquals('attachment; filename="Unread articles.xml"', $headers->get('content-disposition'));
        $this->assertEquals('UTF-8', $headers->get('content-transfer-encoding'));

        $content = new \SimpleXMLElement($client->getResponse()->getContent());
        $this->assertGreaterThan(0, $content->count());
        $this->assertEquals(count($contentInDB), $content->count());
        $this->assertNotEmpty('id', (string) $content->entry[0]->id);
        $this->assertNotEmpty('title', (string) $content->entry[0]->title);
        $this->assertNotEmpty('url', (string) $content->entry[0]->url);
        $this->assertNotEmpty('content', (string) $content->entry[0]->content);
        $this->assertNotEmpty('domain_name', (string) $content->entry[0]->domain_name);
    }
}
