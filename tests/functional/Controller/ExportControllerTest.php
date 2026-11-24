<?php

namespace Wallabag\Tests\Functional\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Helper\EntriesExport;
use Wallabag\Repository\UserRepository;
use Wallabag\Tests\Functional\WallabagTestCase;

class ExportControllerTest extends WallabagTestCase
{
    private $adminEntry;

    private $bobEntry;

    public function testLogin(): void
    {
        $client = $this->getTestClient();

        $client->request('GET', '/export/unread.csv');

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('login', $client->getResponse()->headers->get('location'));
    }

    public function testUnknownCategoryExport(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->request('GET', '/export/awesomeness.epub');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testUnknownFormatExport(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->request('GET', '/export/unread.xslx');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testUnsupportedFormatExport(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->request('GET', '/export/unread.doc');
        $this->assertSame(404, $client->getResponse()->getStatusCode());

        $content = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findOneByUsernameAndNotArchived('admin');

        $client->request('GET', '/export/' . $content->getId() . '.doc');
        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testNonExistingEntryId(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->request('GET', '/export/0.pdf');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testForbiddenEntryId(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(Entry::class)
            ->findOneByUsernameAndNotArchived('bob');

        $client->request('GET', '/export/' . $content->getId() . '.pdf');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testEpubExport(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        ob_start();
        $crawler = $client->request('GET', '/export/archive.epub');
        ob_end_clean();

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertSame('application/epub+zip', $headers->get('content-type'));
        $this->assertSame('attachment; filename="Archive articles.epub"', $headers->get('content-disposition'));
        $this->assertSame('binary', $headers->get('content-transfer-encoding'));
    }

    public function testEpubExportEscapesXmlSpecialCharacters(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $user = $em->getRepository(User::class)->findOneByUserName('admin');

        $entry = new Entry($user);
        $entry->setUrl('http://example.com/article?foo=bar&baz=qux');
        $entry->setTitle('D&D <test> article');
        $entry->setContent('<p>valid content</p>');
        $entry->setLanguage('en');
        $entry->setDomainName('example.com');
        $entry->setMimetype('text/html');
        $entry->setCreatedAt(new \DateTimeImmutable());
        $em->persist($entry);
        $em->flush();

        $response = $client->getContainer()
            ->get(EntriesExport::class)
            ->setEntries($entry)
            ->updateTitle('entry')
            ->updateAuthor('entry')
            ->exportAs('epub');

        $this->assertSame(200, $response->getStatusCode());

        $epubFilename = tempnam(sys_get_temp_dir(), 'wallabag-epub-');
        file_put_contents($epubFilename, $response->getContent());

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($epubFilename));

        $opf = $zip->getFromName('OEBPS/book.opf');
        $this->assertIsString($opf);
        $this->assertXmlStringEqualsXmlString($opf, $opf);
        $this->assertStringContainsString('D&amp;D &lt;test&gt; article', $opf);

        $cover = $zip->getFromName('OEBPS/' . sha1($entry->getUrl() . ':' . $entry->getTitle()) . '_cover.html');
        $this->assertIsString($cover);
        $this->assertXmlStringEqualsXmlString($cover, $cover);
        $this->assertStringContainsString('href="http://example.com/article?foo=bar&amp;baz=qux"', $cover);

        $epub3Toc = $zip->getFromName('OEBPS/epub3toc.xhtml');
        $this->assertIsString($epub3Toc);
        $this->assertXmlStringEqualsXmlString($epub3Toc, $epub3Toc);

        $zip->close();
        unlink($epubFilename);

        $em->remove($entry);
        $em->flush();
    }

    public function testEpubExportDoesNotEmbedReferencedFiles(): void
    {
        $secretFilename = tempnam(sys_get_temp_dir(), 'wallabag-secret-');
        file_put_contents($secretFilename, 'WALLABAG-SECRET-MARKER');
        $imageFilename = tempnam(sys_get_temp_dir(), 'wallabag-image-');
        file_put_contents($imageFilename, $this->getOnePixelPng());

        $baseUrl = $_ENV['WALLABAG_BASE_URL'];
        $files = $this->exportToEpub(
            '<link href="' . $secretFilename . '"/><img src="' . $imageFilename . '"/><img src="' . $baseUrl . '/assets/images/../../img/appicon/apple-touch-icon-152.png"/>',
            $imageFilename,
        );

        unlink($secretFilename);
        unlink($imageFilename);

        foreach ($files as $name => $data) {
            $this->assertStringNotContainsString('OEBPS/images/', $name);
            $this->assertStringNotContainsString('WALLABAG-SECRET-MARKER', $data);
        }
    }

    public function testEpubExportEmbedsImagesStoredByWallabag(): void
    {
        $folder = $this->getTestClient()->getContainer()->getParameter('kernel.project_dir') . '/web/assets/images/test-export';

        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }
        file_put_contents($folder . '/image.png', $this->getOnePixelPng());

        $imageUrl = $_ENV['WALLABAG_BASE_URL'] . '/assets/images/test-export/image.png';
        $files = $this->exportToEpub('<img src="' . $imageUrl . '" srcset="' . $imageUrl . ' 2x"/>', $imageUrl);

        unlink($folder . '/image.png');
        rmdir($folder);

        $this->assertArrayHasKey('OEBPS/images/cover.png', $files);
        $this->assertCount(2, preg_grep('#^OEBPS/images/#', array_keys($files)));
    }

    public function testPdfExport(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        ob_start();
        $crawler = $client->request('GET', '/export/all.pdf');
        ob_end_clean();

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertSame('application/pdf', $headers->get('content-type'));
        $this->assertSame('attachment; filename="All articles.pdf"', $headers->get('content-disposition'));
        $this->assertSame('binary', $headers->get('content-transfer-encoding'));

        ob_start();
        $crawler = $client->request('GET', '/export/tag_entries.pdf?tag=t:foo-bar');
        ob_end_clean();

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertSame('application/pdf', $headers->get('content-type'));
        $this->assertSame('attachment; filename="Tag foo bar articles.pdf"', $headers->get('content-disposition'));
        $this->assertSame('binary', $headers->get('content-transfer-encoding'));
    }

    public function testTxtExport(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        ob_start();
        $crawler = $client->request('GET', '/export/all.txt');
        ob_end_clean();

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertSame('text/plain; charset=UTF-8', $headers->get('content-type'));
        $this->assertSame('attachment; filename="All articles.txt"', $headers->get('content-disposition'));
        $this->assertSame('UTF-8', $headers->get('content-transfer-encoding'));
    }

    public function testCsvExport(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $entry = $em->getRepository(Entry::class)->findByUrlAndUserId('http://0.0.0.0/entry6', $this->getLoggedInUserId());
        $entry->setCreatedAt(new \DateTimeImmutable('2016-02-25 17:38:12'));
        $em->flush();

        // to be sure results are the same
        $contentInDB = $em
            ->getRepository(Entry::class)
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

        $csv = str_getcsv((string) $client->getResponse()->getContent(), "\n", '"', '');

        $this->assertGreaterThan(1, $csv);
        // +1 for title line
        $this->assertCount(\count($contentInDB) + 1, $csv);
        $this->assertSame('Title;URL;Content;Tags;"MIME Type";Language;"Creation date"', $csv[0]);
        $this->assertStringContainsString($contentInDB[0]['title'], $csv[1]);
        $this->assertStringContainsString($contentInDB[0]['url'], $csv[1]);
        $this->assertStringContainsString($contentInDB[0]['content'], $csv[1]);
        $this->assertStringContainsString($contentInDB[0]['mimetype'], $csv[1]);
        $this->assertStringContainsString($contentInDB[0]['language'], $csv[1]);
        $this->assertStringContainsString('25/02/2016 17:38:12', $csv[1]);

        foreach ($contentInDB[0]['tags'] as $tag) {
            $this->assertStringContainsString($tag['label'], $csv[1]);
        }
    }

    public function testJsonExport(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $contentInDB = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId('http://0.0.0.0/entry1', $this->getLoggedInUserId());

        ob_start();
        $crawler = $client->request('GET', '/export/' . $contentInDB->getId() . '.json');
        ob_end_clean();

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertSame('application/json', $headers->get('content-type'));
        $this->assertSame('attachment; filename="' . $this->getSanitizedFilename($contentInDB->getTitle()) . '.json"', $headers->get('content-disposition'));
        $this->assertSame('UTF-8', $headers->get('content-transfer-encoding'));

        $content = json_decode((string) $client->getResponse()->getContent(), true);
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
        $this->assertContains('baz', $content[0]['tags']);
        $this->assertContains('foo', $content[0]['tags']);
    }

    public function testJsonExportFromSearch(): void
    {
        $this->setUpForJsonExportFromSearch();

        $this->logInAs('admin');
        $client = $this->getTestClient();

        ob_start();
        $crawler = $client->request('GET', '/export/search.json?search_entry[term]=entry+search&currentRoute=homepage');
        ob_end_clean();

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertSame('application/json', $headers->get('content-type'));
        $this->assertSame('attachment; filename="Search entry search articles.json"', $headers->get('content-disposition'));
        $this->assertSame('UTF-8', $headers->get('content-transfer-encoding'));

        $content = json_decode((string) $client->getResponse()->getContent(), true);
        $this->assertCount(1, $content);

        $this->tearDownForJsonExportFromSearch();
    }

    public function testXmlExport(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        // to be sure results are the same
        $contentInDB = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
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
        $this->assertCount(\count($contentInDB), $content);
        $this->assertNotEmpty('id', (string) $content->entry[0]->id);
        $this->assertNotEmpty('title', (string) $content->entry[0]->title);
        $this->assertNotEmpty('url', (string) $content->entry[0]->url);
        $this->assertNotEmpty('content', (string) $content->entry[0]->content);
        $this->assertNotEmpty('domain_name', (string) $content->entry[0]->domain_name);
        $this->assertNotEmpty('created_at', (string) $content->entry[0]->created_at);
        $this->assertNotEmpty('updated_at', (string) $content->entry[0]->updated_at);
    }

    public function testMdExport(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $client->request('GET', '/export/all.md');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $content = $client->getResponse()->getContent();
        $this->assertSame('text/markdown; charset=UTF-8', $headers->get('content-type'));
        $this->assertSame('attachment; filename="All articles.md"', $headers->get('content-disposition'));
        $this->assertSame('UTF-8', $headers->get('content-transfer-encoding'));
        $this->assertStringContainsString('=================', $content);
    }

    public function testJsonExportFromSameDomain(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $entry = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId('http://0.0.0.0/entry1', $this->getLoggedInUserId());

        \assert($entry instanceof Entry);

        ob_start();
        $crawler = $client->request('GET', '/export/same_domain.json?entry=' . $entry->getId());
        ob_end_clean();

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertSame('application/json', $headers->get('content-type'));
        $this->assertSame('attachment; filename="Same domain articles.json"', $headers->get('content-disposition'));
        $this->assertSame('UTF-8', $headers->get('content-transfer-encoding'));

        $content = json_decode((string) $client->getResponse()->getContent(), true);
        $this->assertCount(4, $content);
    }

    public function testJsonExportLinkFromSameDomainPreservesReferenceEntry(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->findOneBy(['username' => 'admin']);

        $reference = new Entry($user);
        $reference->setUrl('http://same-domain-export.test/reference');
        $reference->setDomainName('same-domain-export.test');
        $reference->setTitle('Reference entry');
        $em->persist($reference);

        $sameDomainEntries = [];

        foreach (['first', 'second'] as $name) {
            $entry = new Entry($user);
            $entry->setUrl('http://same-domain-export.test/' . $name);
            $entry->setDomainName('same-domain-export.test');
            $entry->setTitle('Same domain ' . $name);
            $em->persist($entry);
            $sameDomainEntries[] = $entry;
        }

        $otherDomainEntry = new Entry($user);
        $otherDomainEntry->setUrl('http://other-domain-export.test/article');
        $otherDomainEntry->setDomainName('other-domain-export.test');
        $otherDomainEntry->setTitle('Other domain');
        $em->persist($otherDomainEntry);
        $em->flush();
        $referenceId = $reference->getId();
        $sameDomainEntryIds = array_map(static fn (Entry $entry): int => $entry->getId(), $sameDomainEntries);
        $otherDomainEntryId = $otherDomainEntry->getId();

        $crawler = $client->request('GET', '/domain/' . $referenceId);
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(2, $crawler->filter('ol.entries > li'));

        $exportLink = $crawler->filter('#export a[href*="/export/same_domain.json"]');
        $this->assertCount(1, $exportLink);

        $query = [];
        parse_str((string) parse_url($exportLink->attr('href'), \PHP_URL_QUERY), $query);
        $this->assertSame((string) $referenceId, $query['entry']);

        $client->click($exportLink->link());
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $content = json_decode((string) $client->getResponse()->getContent(), true);
        $this->assertEqualsCanonicalizing(
            $sameDomainEntryIds,
            array_column($content, 'id'),
        );
        $this->assertNotContains($otherDomainEntryId, array_column($content, 'id'));

        $cleanupEm = $client->getContainer()->get(EntityManagerInterface::class);

        foreach ([$referenceId, ...$sameDomainEntryIds, $otherDomainEntryId] as $entryId) {
            $cleanupEm->remove($cleanupEm->find(Entry::class, $entryId));
        }
        $cleanupEm->flush();
    }

    /**
     * @dataProvider searchExportContexts
     */
    public function testJsonExportLinkFromSearchPreservesContext(string $currentRoute, array $expectedKeys): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $em = $this->getEntityManager();
        $user = $em->getRepository(User::class)->findOneBy(['username' => 'admin']);

        $entries = [];

        foreach ([
            'starred-unread' => ['starred' => true, 'archived' => false],
            'starred-archive' => ['starred' => true, 'archived' => true],
            'unstarred-unread' => ['starred' => false, 'archived' => false],
            'unstarred-archive' => ['starred' => false, 'archived' => true],
        ] as $key => $state) {
            $entry = new Entry($user);
            $entry->setUrl('http://search-export.test/' . $key);
            $entry->setTitle('search-export-context-needle ' . $key);
            $entry->setContent('search-export-context-needle');
            $entry->setStarred($state['starred']);
            $entry->updateArchived($state['archived']);
            $em->persist($entry);
            $entries[$key] = $entry;
        }
        $em->flush();
        $entryIds = array_map(static fn (Entry $entry): int => $entry->getId(), $entries);

        $searchUrl = '/search?search_entry%5Bterm%5D=search-export-context-needle&currentRoute=' . $currentRoute;
        $crawler = $client->request('GET', $searchUrl);
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(\count($expectedKeys), $crawler->filter('[data-test="entry"]'));

        $exportLink = $crawler->filter('#export a[href*="/export/search.json"]');
        $this->assertCount(1, $exportLink);
        $query = [];
        parse_str((string) parse_url($exportLink->attr('href'), \PHP_URL_QUERY), $query);
        $this->assertSame('search-export-context-needle', $query['search_entry']['term']);
        $this->assertSame($currentRoute, $query['currentRoute']);

        $client->click($exportLink->link());
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $content = json_decode((string) $client->getResponse()->getContent(), true);
        $this->assertEqualsCanonicalizing(
            array_map(static fn (string $key): int => $entryIds[$key], $expectedKeys),
            array_column($content, 'id'),
        );

        $cleanupEm = $client->getContainer()->get(EntityManagerInterface::class);

        foreach ($entryIds as $entryId) {
            $cleanupEm->remove($cleanupEm->find(Entry::class, $entryId));
        }
        $cleanupEm->flush();
    }

    public static function searchExportContexts(): iterable
    {
        yield 'starred filter' => ['starred', ['starred-unread', 'starred-archive']];
        yield 'unread filter' => ['unread', ['starred-unread', 'unstarred-unread']];
        yield 'archive filter' => ['archive', ['starred-archive', 'unstarred-archive']];
    }

    private function setUpForJsonExportFromSearch(): void
    {
        $em = $this->getEntityManager();

        $userRepository = static::getContainer()->get(UserRepository::class);

        $user = $userRepository->findOneByUserName('admin');
        $this->adminEntry = new Entry($user);
        $this->adminEntry->setUrl('http://0.0.0.0/entry-search-admin');
        $this->adminEntry->setTitle('test title entry search admin');
        $this->adminEntry->setContent('this is my content /o/');
        $em->persist($this->adminEntry);

        $user = $userRepository->findOneByUserName('bob');
        $this->bobEntry = new Entry($user);
        $this->bobEntry->setUrl('http://0.0.0.0/entry-search-bob');
        $this->bobEntry->setTitle('test title entry search bob');
        $this->bobEntry->setContent('this is my content /o/');
        $em->persist($this->bobEntry);

        $em->flush();
    }

    private function tearDownForJsonExportFromSearch(): void
    {
        $em = $this->getEntityManager();

        $em->remove($this->adminEntry);
        $em->remove($this->bobEntry);

        $em->flush();
    }

    private function getSanitizedFilename($title)
    {
        $transliterator = \Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;', \Transliterator::FORWARD);

        return preg_replace('/[^A-Za-z0-9\- \']/', '', $transliterator->transliterate($title));
    }

    private function exportToEpub(string $content, ?string $previewPicture = null): array
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $user = $client->getContainer()->get(EntityManagerInterface::class)->getRepository(User::class)->findOneByUserName('admin');

        $entry = new Entry($user);
        $entry->setUrl('http://example.com/article');
        $entry->setTitle('article');
        $entry->setContent($content);
        $entry->setPreviewPicture($previewPicture);
        $entry->setLanguage('en');
        $entry->setDomainName('example.com');
        $entry->setMimetype('text/html');
        $entry->setCreatedAt(new \DateTimeImmutable());

        $response = $client->getContainer()
            ->get(EntriesExport::class)
            ->setEntries($entry)
            ->updateTitle('entry')
            ->updateAuthor('entry')
            ->exportAs('epub');

        $epubFilename = tempnam(sys_get_temp_dir(), 'wallabag-epub-');
        file_put_contents($epubFilename, $response->getContent());

        $zip = new \ZipArchive();
        $zip->open($epubFilename);

        $files = [];

        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $files[$zip->getNameIndex($i)] = $zip->getFromIndex($i);
        }

        $zip->close();
        unlink($epubFilename);

        return $files;
    }

    private function getOnePixelPng(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==', true);
    }
}
