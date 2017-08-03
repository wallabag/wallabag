<?php

namespace Tests\Wallabag\CoreBundle\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;

class ExportControllerTest extends WallabagCoreTestCase
{
    public function testLogin()
    {
        $client = $this->getClient();

        $client->request('GET', '/export/unread.csv');

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertContains('login', $client->getResponse()->headers->get('location'));
    }

    public function testUnknownCategoryExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/export/awesomeness.epub');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testUnknownFormatExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/export/unread.xslx');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testUnsupportedFormatExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/export/unread.doc');
        $this->assertSame(404, $client->getResponse()->getStatusCode());

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUsernameAndNotArchived('admin');

        $client->request('GET', '/export/' . $content->getId() . '.doc');
        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testBadEntryId()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request('GET', '/export/0.mobi');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testEpubExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        ob_start();
        $crawler = $client->request('GET', '/export/archive.epub');
        ob_end_clean();

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertSame('application/epub+zip', $headers->get('content-type'));
        $this->assertSame('attachment; filename="Archive articles.epub"', $headers->get('content-disposition'));
        $this->assertSame('binary', $headers->get('content-transfer-encoding'));
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
        $crawler = $client->request('GET', '/export/' . $content->getId() . '.mobi');
        ob_end_clean();

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertSame('application/x-mobipocket-ebook', $headers->get('content-type'));
        $this->assertSame('attachment; filename="' . preg_replace('/[^A-Za-z0-9\-]/', '', $content->getTitle()) . '.mobi"', $headers->get('content-disposition'));
        $this->assertSame('binary', $headers->get('content-transfer-encoding'));
    }

    public function testPdfExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        ob_start();
        $crawler = $client->request('GET', '/export/all.pdf');
        ob_end_clean();

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertSame('application/pdf', $headers->get('content-type'));
        $this->assertSame('attachment; filename="All articles.pdf"', $headers->get('content-disposition'));
        $this->assertSame('binary', $headers->get('content-transfer-encoding'));

        ob_start();
        $crawler = $client->request('GET', '/export/tag_entries.pdf?tag=foo-bar');
        ob_end_clean();

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertSame('application/pdf', $headers->get('content-type'));
        $this->assertSame('attachment; filename="Tag_entries articles.pdf"', $headers->get('content-disposition'));
        $this->assertSame('binary', $headers->get('content-transfer-encoding'));
    }

    public function testTxtExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        ob_start();
        $crawler = $client->request('GET', '/export/all.txt');
        ob_end_clean();

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertSame('text/plain; charset=UTF-8', $headers->get('content-type'));
        $this->assertSame('attachment; filename="All articles.txt"', $headers->get('content-disposition'));
        $this->assertSame('UTF-8', $headers->get('content-transfer-encoding'));
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
            ->select('e, t')
            ->leftJoin('e.user', 'u')
            ->leftJoin('e.tags', 't')
            ->where('u.username = :username')->setParameter('username', 'admin')
            ->andWhere('e.isArchived = true')
            ->getQuery()
            ->getArrayResult();

        ob_start();
        $crawler = $client->request('GET', '/export/archive.csv');
        ob_end_clean();

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertSame('application/csv', $headers->get('content-type'));
        $this->assertSame('attachment; filename="Archive articles.csv"', $headers->get('content-disposition'));
        $this->assertSame('UTF-8', $headers->get('content-transfer-encoding'));

        $csv = str_getcsv($client->getResponse()->getContent(), "\n");

        $this->assertGreaterThan(1, $csv);
        // +1 for title line
        $this->assertSame(count($contentInDB) + 1, count($csv));
        $this->assertSame('Title;URL;Content;Tags;"MIME Type";Language;"Creation date"', $csv[0]);
        $this->assertContains($contentInDB[0]['title'], $csv[1]);
        $this->assertContains($contentInDB[0]['url'], $csv[1]);
        $this->assertContains($contentInDB[0]['content'], $csv[1]);
        $this->assertContains($contentInDB[0]['mimetype'], $csv[1]);
        $this->assertContains($contentInDB[0]['language'], $csv[1]);
        $this->assertContains($contentInDB[0]['createdAt']->format('d/m/Y h:i:s'), $csv[1]);

        foreach ($contentInDB[0]['tags'] as $tag) {
            $this->assertContains($tag['label'], $csv[1]);
        }
    }

    public function testJsonExport()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $contentInDB = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId('http://0.0.0.0/entry1', $this->getLoggedInUserId());

        ob_start();
        $crawler = $client->request('GET', '/export/' . $contentInDB->getId() . '.json');
        ob_end_clean();

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertSame('application/json', $headers->get('content-type'));
        $this->assertSame('attachment; filename="' . $contentInDB->getTitle() . '.json"', $headers->get('content-disposition'));
        $this->assertSame('UTF-8', $headers->get('content-transfer-encoding'));

        $content = json_decode($client->getResponse()->getContent(), true);
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
        $this->assertArrayHasKey('created_at', $content[0]);
        $this->assertArrayHasKey('updated_at', $content[0]);

        $this->assertSame((int) $contentInDB->isArchived(), $content[0]['is_archived']);
        $this->assertSame((int) $contentInDB->isStarred(), $content[0]['is_starred']);
        $this->assertSame($contentInDB->getTitle(), $content[0]['title']);
        $this->assertSame($contentInDB->getUrl(), $content[0]['url']);
        $this->assertSame([['text' => 'This is my annotation /o/', 'quote' => 'content']], $content[0]['annotations']);
        $this->assertSame($contentInDB->getMimetype(), $content[0]['mimetype']);
        $this->assertSame($contentInDB->getLanguage(), $content[0]['language']);
        $this->assertSame($contentInDB->getReadingtime(), $content[0]['reading_time']);
        $this->assertSame($contentInDB->getDomainname(), $content[0]['domain_name']);
        $this->assertSame(['baz', 'foo'], $content[0]['tags']);
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

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertSame('application/xml', $headers->get('content-type'));
        $this->assertSame('attachment; filename="Unread articles.xml"', $headers->get('content-disposition'));
        $this->assertSame('UTF-8', $headers->get('content-transfer-encoding'));

        $content = new \SimpleXMLElement($client->getResponse()->getContent());
        $this->assertGreaterThan(0, $content->count());
        $this->assertSame(count($contentInDB), $content->count());
        $this->assertNotEmpty('id', (string) $content->entry[0]->id);
        $this->assertNotEmpty('title', (string) $content->entry[0]->title);
        $this->assertNotEmpty('url', (string) $content->entry[0]->url);
        $this->assertNotEmpty('content', (string) $content->entry[0]->content);
        $this->assertNotEmpty('domain_name', (string) $content->entry[0]->domain_name);
        $this->assertNotEmpty('created_at', (string) $content->entry[0]->created_at);
        $this->assertNotEmpty('updated_at', (string) $content->entry[0]->updated_at);
    }
}
