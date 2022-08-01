<?php

namespace Tests\Wallabag\CoreBundle\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;
use Wallabag\CoreBundle\Entity\Entry;
use Wallabag\UserBundle\Entity\User;

class FeedControllerTest extends WallabagCoreTestCase
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
        $this->assertStringContainsString('logo-square.png', $xpath->query('/a:feed/a:logo')->item(0)->nodeValue);

        $this->assertSame(1, $xpath->query('/a:feed/a:updated')->length);

        $this->assertSame(1, $xpath->query('/a:feed/a:generator')->length);
        $this->assertSame('wallabag', $xpath->query('/a:feed/a:generator')->item(0)->nodeValue);
        $this->assertSame('admin', $xpath->query('/a:feed/a:author/a:name')->item(0)->nodeValue);

        $this->assertSame(1, $xpath->query('/a:feed/a:subtitle')->length);
        if (null !== $tagValue && 0 === strpos($type, 'tag')) {
            $this->assertSame('wallabag — ' . $type . ' ' . $tagValue . ' feed', $xpath->query('/a:feed/a:title')->item(0)->nodeValue);
            $this->assertSame('Atom feed for entries tagged with ' . $tagValue, $xpath->query('/a:feed/a:subtitle')->item(0)->nodeValue);
        } else {
            $this->assertSame('wallabag — ' . $type . ' feed', $xpath->query('/a:feed/a:title')->item(0)->nodeValue);
            $this->assertSame('Atom feed for ' . $type . ' entries', $xpath->query('/a:feed/a:subtitle')->item(0)->nodeValue);
        }

        $this->assertSame(1, $xpath->query('/a:feed/a:link[@rel="self"]')->length);
        $this->assertStringContainsString($type, $xpath->query('/a:feed/a:link[@rel="self"]')->item(0)->getAttribute('href'));

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
        $client = $this->getClient();

        $client->request('GET', $url);

        $this->assertSame(404, $client->getResponse()->getStatusCode());
    }

    public function testUnread()
    {
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
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
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');

        $config = $user->getConfig();
        $config->setFeedToken('SUPERTOKEN');
        $config->setFeedLimit(1);
        $em->persist($config);
        $em->flush();

        $client = $this->getClient();
        $client->request('GET', '/feed/admin/SUPERTOKEN/starred');

        $this->assertSame(200, $client->getResponse()->getStatusCode(), 1);

        $this->validateDom($client->getResponse()->getContent(), 'starred');
    }

    public function testArchives()
    {
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');

        $config = $user->getConfig();
        $config->setFeedToken('SUPERTOKEN');
        $config->setFeedLimit(null);
        $em->persist($config);
        $em->flush();

        $client = $this->getClient();
        $client->request('GET', '/feed/admin/SUPERTOKEN/archive');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->validateDom($client->getResponse()->getContent(), 'archive');
    }

    public function testAll()
    {
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');

        $config = $user->getConfig();
        $config->setFeedToken('SUPERTOKEN');
        $config->setFeedLimit(null);
        $em->persist($config);
        $em->flush();

        $client = $this->getClient();
        $client->request('GET', '/feed/admin/SUPERTOKEN/all');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->validateDom($client->getResponse()->getContent(), 'all');
    }

    public function testPagination()
    {
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');

        $config = $user->getConfig();
        $config->setFeedToken('SUPERTOKEN');
        $config->setFeedLimit(1);
        $em->persist($config);
        $em->flush();

        $client = $this->getClient();

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
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
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

        $property = (new \ReflectionObject($entry1))->getProperty('updatedAt');
        $property->setAccessible(true);
        $property->setValue($entry1, $day4);

        $property = (new \ReflectionObject($entry4))->getProperty('updatedAt');
        $property->setAccessible(true);
        $property->setValue($entry4, $day3);

        $em->flush();

        $client = $this->getClient();

        // tag foo - without sort
        $crawler = $client->request('GET', '/feed/admin/SUPERTOKEN/tags/foo');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('test title entry4', $crawler->filterXPath('//feed/entry[1]/title')->text());
        $this->assertSame('test title entry1', $crawler->filterXPath('//feed/entry[2]/title')->text());

        // tag foo - with sort created
        $crawler = $client->request('GET', '/feed/admin/SUPERTOKEN/tags/foo?sort=created');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('test title entry4', $crawler->filterXPath('//feed/entry[1]/title')->text());
        $this->assertSame('test title entry1', $crawler->filterXPath('//feed/entry[2]/title')->text());

        // tag foo - with sort updated
        $crawler = $client->request('GET', '/feed/admin/SUPERTOKEN/tags/foo?sort=updated');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('test title entry1', $crawler->filterXPath('//feed/entry[1]/title')->text());
        $this->assertSame('test title entry4', $crawler->filterXPath('//feed/entry[2]/title')->text());

        // tag foo - with invalid sort
        $client->request('GET', '/feed/admin/SUPERTOKEN/tags/foo?sort=invalid');
        $this->assertSame(400, $client->getResponse()->getStatusCode());

        // tag foo/3000
        $client->request('GET', '/feed/admin/SUPERTOKEN/tags/foo/3000');
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
        $client = $this->getClient();

        $client->request('GET', $url);

        $this->assertSame(301, $client->getResponse()->getStatusCode());
    }
}
