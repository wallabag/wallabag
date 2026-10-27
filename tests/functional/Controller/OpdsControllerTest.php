<?php

namespace Wallabag\Tests\Functional\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;
use Wallabag\Tests\Functional\WallabagTestCase;

class OpdsControllerTest extends WallabagTestCase
{
    public function testRootNavigationFeed(): void
    {
        $client = $this->getTestClient();
        $this->setFeedToken($client, 'admin', 'SUPERTOKEN');

        $client->request('GET', '/opds/admin/SUPERTOKEN');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('application/atom+xml', $client->getResponse()->headers->get('content-type'));

        $xpath = $this->xpath($client->getResponse()->getContent());

        $this->assertSame(1, $xpath->query('/a:feed')->length);
        // One digest acquisition entry + 4 shelf navigation entries.
        $this->assertSame(5, $xpath->query('//a:entry')->length);

        $digestLinks = $xpath->query('//a:entry/a:link[@rel="http://opds-spec.org/acquisition" and @type="application/epub+zip"]');
        $this->assertSame(1, $digestLinks->length, 'root feed exposes one digest acquisition link');
        $this->assertStringContainsString('/digest.epub', $digestLinks->item(0)->attributes['href']->value);

        $this->assertSame(4, $xpath->query('//a:entry/a:link[@rel="subsection"]')->length, 'root feed exposes 4 shelf links');
    }

    public function dataForShelf(): array
    {
        return [
            ['unread'],
            ['starred'],
            ['archive'],
            ['all'],
        ];
    }

    /**
     * @dataProvider dataForShelf
     */
    public function testShelfAcquisitionFeed(string $type): void
    {
        $client = $this->getTestClient();
        $this->setFeedToken($client, 'admin', 'SUPERTOKEN');

        $client->request('GET', '/opds/admin/SUPERTOKEN/' . $type);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('application/atom+xml', $client->getResponse()->headers->get('content-type'));

        $content = $client->getResponse()->getContent();
        // Titles/authors must be plain escaped text, never CDATA: CDATA + |e
        // double-encodes (e.g. an apostrophe renders as "&#039;" on the device).
        $this->assertStringNotContainsString('<![CDATA[', $content);

        $xpath = $this->xpath($content);

        $this->assertSame(1, $xpath->query('/a:feed')->length);
        // Element contract: each entry carries exactly one epub acquisition link with a type attribute.
        foreach ($xpath->query('//a:entry') as $entry) {
            $links = $xpath->query('a:link[@rel="http://opds-spec.org/acquisition" and @type="application/epub+zip"]', $entry);
            $this->assertSame(1, $links->length);
            $this->assertStringContainsString('/entries/', $links->item(0)->attributes['href']->value);
        }
    }

    public function testShelfPaginationRelLinks(): void
    {
        $client = $this->getTestClient();
        // feedLimit 1 forces multiple pages so next/previous appear.
        $this->setFeedToken($client, 'admin', 'SUPERTOKEN', 1);

        $client->request('GET', '/opds/admin/SUPERTOKEN/all/1');
        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $xpath = $this->xpath($client->getResponse()->getContent());
        $this->assertSame(1, $xpath->query('/a:feed/a:link[@rel="last"]')->length);
        // Crosspoint parses rel="next" / rel="previous" (not "prev").
        $this->assertSame(1, $xpath->query('/a:feed/a:link[@rel="next"]')->length, 'first page has a next link');
        $this->assertSame(0, $xpath->query('/a:feed/a:link[@rel="previous"]')->length, 'first page has no previous link');

        $client->request('GET', '/opds/admin/SUPERTOKEN/all/2');
        $xpath = $this->xpath($client->getResponse()->getContent());
        $this->assertSame(1, $xpath->query('/a:feed/a:link[@rel="previous"]')->length, 'second page has a previous link');
    }

    public function testDigestEpub(): void
    {
        $client = $this->getTestClient();
        $this->setFeedToken($client, 'admin', 'SUPERTOKEN');

        $client->request('GET', '/opds/admin/SUPERTOKEN/digest.epub');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $headers = $client->getResponse()->headers;
        $this->assertSame('application/epub+zip', $headers->get('content-type'));
        $this->assertStringContainsString('.epub', $headers->get('content-disposition'));
        $this->assertNotEmpty($client->getResponse()->getContent());
    }

    public function testDigestLimitIsClamped(): void
    {
        $client = $this->getTestClient();
        $this->setFeedToken($client, 'admin', 'SUPERTOKEN');

        // Out-of-range values must not error; they clamp to [1, 50].
        $client->request('GET', '/opds/admin/SUPERTOKEN/digest.epub?limit=999');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('application/epub+zip', $client->getResponse()->headers->get('content-type'));

        $client->request('GET', '/opds/admin/SUPERTOKEN/digest.epub?limit=0');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('application/epub+zip', $client->getResponse()->headers->get('content-type'));
    }

    public function testSingleEntryExport(): void
    {
        $client = $this->getTestClient();
        $this->setFeedToken($client, 'admin', 'SUPERTOKEN');

        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $admin = $em->getRepository(User::class)->findOneByUsername('admin');
        $entry = $em->getRepository(Entry::class)->findOneBy(['user' => $admin]);
        $this->assertNotNull($entry, 'fixtures provide at least one entry for admin');

        $client->request('GET', '/opds/admin/SUPERTOKEN/entries/' . $entry->getId() . '.epub');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('application/epub+zip', $client->getResponse()->headers->get('content-type'));
        $this->assertNotEmpty($client->getResponse()->getContent());
    }

    public function dataForBadToken(): array
    {
        return [
            ['/opds/admin/WRONGTOKEN'],
            ['/opds/admin/WRONGTOKEN/unread'],
            ['/opds/admin/WRONGTOKEN/digest.epub'],
            ['/opds/admin/WRONGTOKEN/entries/1.epub'],
        ];
    }

    /**
     * @dataProvider dataForBadToken
     */
    public function testBadTokenIsRejected(string $url): void
    {
        $client = $this->getTestClient();
        $this->setFeedToken($client, 'admin', 'SUPERTOKEN');

        $client->request('GET', $url);

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testCrossUserEntryIsNotFound(): void
    {
        $client = $this->getTestClient();
        $this->setFeedToken($client, 'admin', 'SUPERTOKEN');

        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $admin = $em->getRepository(User::class)->findOneByUsername('admin');

        // An entry owned by someone other than admin must 404 under admin's token.
        $otherEntry = $em->getRepository(Entry::class)->createQueryBuilder('e')
            ->where('e.user != :user')->setParameter('user', $admin)
            ->setMaxResults(1)->getQuery()->getOneOrNullResult();
        $this->assertNotNull($otherEntry, 'fixtures provide an entry owned by another user');

        $client->request('GET', '/opds/admin/SUPERTOKEN/entries/' . $otherEntry->getId() . '.epub');

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testConfigPageLinksToCatalog(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $this->setFeedToken($client, 'admin', 'SUPERTOKEN');

        $crawler = $client->request('GET', '/config');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertCount(1, $crawler->filter('a[href="/opds/admin/SUPERTOKEN"]'));
    }

    private function setFeedToken($client, string $username, string $token, int $limit = 2): void
    {
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $user = $em->getRepository(User::class)->findOneByUsername($username);

        $config = $user->getConfig();
        $config->setFeedToken($token);
        $config->setFeedLimit($limit);
        $em->persist($config);
        $em->flush();
    }

    private function xpath(string $xml): \DOMXPath
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('a', 'http://www.w3.org/2005/Atom');

        return $xpath;
    }
}
