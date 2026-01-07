<?php

namespace Tests\Wallabag\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\Entry;
use Wallabag\Entity\User;

class FeedControllerTest extends WallabagTestCase
{
    public function validateDom($xml, $type, $nb = null, $tagValue = null)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('a', 'http://www.w3.org/2005/Atom');

        if (null === $nb) {
            $this->assertGreaterThan(0, $xpath->query('//a:entry')->length);
        } else {
            $this->assertSame($nb, $xpath->query('//a:entry')->length);
        }

        $this->assertSame(1, $xpath->query('/a:feed')->length);

        $this->assertSame(1, $xpath->query('/a:feed/a:title')->length);
        $this->assertStringContainsString('favicon.ico', $xpath->query('/a:feed/a:icon')->item(0)->nodeValue);
        $this->assertStringContainsString('logo-square.svg', $xpath->query('/a:feed/a:logo')->item(0)->nodeValue);

        $this->assertSame(1, $xpath->query('/a:feed/a:updated')->length);

        $this->assertSame(1, $xpath->query('/a:feed/a:generator')->length);
        $this->assertSame('wallabag', $xpath->query('/a:feed/a:generator')->item(0)->nodeValue);
        $this->assertSame('admin', $xpath->query('/a:feed/a:author/a:name')->item(0)->nodeValue);

        $this->assertSame(1, $xpath->query('/a:feed/a:subtitle')->length);
        if (null !== $tagValue && str_starts_with((string) $type, 'tag')) {
            $this->assertSame('wallabag — ' . $type . ' ' . $tagValue . ' feed', $xpath->query('/a:feed/a:title')->item(0)->nodeValue);
            $this->assertSame('Atom feed for entries tagged with ' . $tagValue, $xpath->query('/a:feed/a:subtitle')->item(0)->nodeValue);
        } else {
            $this->assertSame('wallabag — ' . $type . ' feed', $xpath->query('/a:feed/a:title')->item(0)->nodeValue);
            $this->assertSame('Atom feed for ' . $type . ' entries', $xpath->query('/a:feed/a:subtitle')->item(0)->nodeValue);
        }

        $this->assertSame(1, $xpath->query('/a:feed/a:link[@rel="self"]')->length);
        $this->assertStringContainsString($type, $xpath->query('/a:feed/a:link[@rel="self"]')->item(0)->attributes['href']->value);

        $this->assertSame(1, $xpath->query('/a:feed/a:link[@rel="last"]')->length);

        foreach ($xpath->query('//a:entry') as $item) {
            $this->assertSame(1, $xpath->query('a:title', $item)->length);
            $this->assertSame(1, $xpath->query('a:link[@rel="via"]', $item)->length);
            $this->assertSame(1, $xpath->query('a:link[@rel="alternate"]', $item)->length);
            $this->assertSame(1, $xpath->query('a:id', $item)->length);
            $this->assertSame(1, $xpath->query('a:published', $item)->length);
            $this->assertSame(1, $xpath->query('a:content', $item)->length);
        }
    }

    public function dataForBadUrl()
    {
        return [
            [
                '/feed/admin/YZIOAUZIAO/unread',
            ],
            [
                '/feed/wallace/YZIOAUZIAO/starred',
            ],
            [
                '/feed/wallace/YZIOAUZIAO/archives',
            ],
            [
                '/feed/wallace/YZIOAUZIAO/all',
            ],
        ];
    }

    /**
     * @dataProvider dataForBadUrl
     */
    public function testBadUrl($url)
    {
        $client = $this->getTestClient();

        $client->request('GET', $url);

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testUnread()
    {
        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');

        $config = $user->getConfig();
        $config->setFeedToken('SUPERTOKEN');
        $config->setFeedLimit(2);
        $em->persist($config);
        $em->flush();

        $client->request('GET', '/feed/admin/SUPERTOKEN/unread');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->validateDom($client->getResponse()->getContent(), 'unread', 2);
    }

    public function testStarred()
    {
        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');

        $config = $user->getConfig();
        $config->setFeedToken('SUPERTOKEN');
        $config->setFeedLimit(1);
        $em->persist($config);
        $em->flush();

        $client = $this->getTestClient();
        $client->request('GET', '/feed/admin/SUPERTOKEN/starred');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->validateDom($client->getResponse()->getContent(), 'starred');
    }

    public function testArchives()
    {
        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');

        $config = $user->getConfig();
        $config->setFeedToken('SUPERTOKEN');
        $config->setFeedLimit(null);
        $em->persist($config);
        $em->flush();

        $client = $this->getTestClient();
        $client->request('GET', '/feed/admin/SUPERTOKEN/archive');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->validateDom($client->getResponse()->getContent(), 'archive');
    }

    public function testAll()
    {
        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');

        $config = $user->getConfig();
        $config->setFeedToken('SUPERTOKEN');
        $config->setFeedLimit(null);
        $em->persist($config);
        $em->flush();

        $client = $this->getTestClient();
        $client->request('GET', '/feed/admin/SUPERTOKEN/all');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->validateDom($client->getResponse()->getContent(), 'all');
    }

    public function testPagination()
    {
        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');

        $config = $user->getConfig();
        $config->setFeedToken('SUPERTOKEN');
        $config->setFeedLimit(1);
        $em->persist($config);
        $em->flush();

        $client = $this->getTestClient();

        $client->request('GET', '/feed/admin/SUPERTOKEN/unread');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->validateDom($client->getResponse()->getContent(), 'unread');

        $client->request('GET', '/feed/admin/SUPERTOKEN/unread/2');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->validateDom($client->getResponse()->getContent(), 'unread');

        $client->request('GET', '/feed/admin/SUPERTOKEN/unread/3000');
        $this->assertSame(302, $client->getResponse()->getStatusCode());
    }

    public function testTags()
    {
        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');

        $config = $user->getConfig();
        $config->setFeedToken('SUPERTOKEN');
        $config->setFeedLimit(null);
        $em->persist($config);

        $entry1 = $em
            ->getRepository(Entry::class)
            ->find(1)
        ;

        $entry4 = $em
            ->getRepository(Entry::class)
            ->find(4)
        ;

        $now = new \DateTimeImmutable('now');

        $day1 = $now->modify('-8 days');
        $day2 = $now->modify('-6 days');
        $day3 = $now->modify('-4 days');
        $day4 = $now->modify('-2 days');

        $entry1->setCreatedAt($day1);
        $entry4->setCreatedAt($day2);

        $property = (new \ReflectionObject($entry4))->getProperty('updatedAt');
        $property->setAccessible(true);
        $property->setValue($entry4, $day3);

        // We have to flush and sleep here to be sure that $entry1 and $entry4 have different updatedAt values
        $em->flush();
        sleep(2);

        $property = (new \ReflectionObject($entry1))->getProperty('updatedAt');
        $property->setAccessible(true);
        $property->setValue($entry1, $day4);

        $em->flush();

        $client = $this->getTestClient();

        // tag foo - without sort
        $crawler = $client->request('GET', '/feed/admin/SUPERTOKEN/tags/t:foo');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('test title entry4', $crawler->filterXPath('//feed/entry[1]/title')->text());
        $this->assertSame('test title entry1', $crawler->filterXPath('//feed/entry[2]/title')->text());

        // tag foo - with sort created
        $crawler = $client->request('GET', '/feed/admin/SUPERTOKEN/tags/t:foo?sort=created');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('test title entry4', $crawler->filterXPath('//feed/entry[1]/title')->text());
        $this->assertSame('test title entry1', $crawler->filterXPath('//feed/entry[2]/title')->text());

        // tag foo - with sort updated
        $crawler = $client->request('GET', '/feed/admin/SUPERTOKEN/tags/t:foo?sort=updated');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('test title entry1', $crawler->filterXPath('//feed/entry[1]/title')->text());
        $this->assertSame('test title entry4', $crawler->filterXPath('//feed/entry[2]/title')->text());

        // tag foo - with invalid sort
        $client->request('GET', '/feed/admin/SUPERTOKEN/tags/t:foo?sort=invalid');
        $this->assertSame(400, $client->getResponse()->getStatusCode());

        // tag foo/3000
        $client->request('GET', '/feed/admin/SUPERTOKEN/tags/t:foo/3000');
        $this->assertSame(302, $client->getResponse()->getStatusCode());
    }

    public function dataForRedirect()
    {
        return [
            [
                '/admin/YZIOAUZIAO/unread.xml',
            ],
            [
                '/admin/YZIOAUZIAO/starred.xml',
            ],
            [
                '/admin/YZIOAUZIAO/archive.xml',
            ],
            [
                '/admin/YZIOAUZIAO/all.xml',
            ],
            [
                '/admin/YZIOAUZIAO/tags/foo.xml',
            ],
        ];
    }

    /**
     * @dataProvider dataForRedirect
     */
    public function testRedirectFromRssToAtom($url)
    {
        $client = $this->getTestClient();

        $client->request('GET', $url);

        $this->assertSame(301, $client->getResponse()->getStatusCode());
    }
}
