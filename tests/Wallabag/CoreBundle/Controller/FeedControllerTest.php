<?php

namespace Tests\Wallabag\CoreBundle\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;

class FeedControllerTest extends WallabagCoreTestCase
{
    public function validateDom($xml, $type, $nb = null, $tagValue = null)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xml);

        $xpath = new \DOMXpath($doc);
        $xpath->registerNamespace('a', 'http://www.w3.org/2005/Atom');

        if (null === $nb) {
            $this->assertGreaterThan(0, $xpath->query('//a:entry')->length);
        } else {
            $this->assertEquals($nb, $xpath->query('//a:entry')->length);
        }

        $this->assertEquals(1, $xpath->query('/a:feed')->length);

        $this->assertEquals(1, $xpath->query('/a:feed/a:title')->length);
        $this->assertContains('favicon.ico', $xpath->query('/a:feed/a:icon')->item(0)->nodeValue);
        $this->assertContains('logo-square.png', $xpath->query('/a:feed/a:logo')->item(0)->nodeValue);

        $this->assertEquals(1, $xpath->query('/a:feed/a:updated')->length);

        $this->assertEquals(1, $xpath->query('/a:feed/a:generator')->length);
        $this->assertEquals('wallabag', $xpath->query('/a:feed/a:generator')->item(0)->nodeValue);
        $this->assertEquals('admin', $xpath->query('/a:feed/a:author/a:name')->item(0)->nodeValue);

        $this->assertEquals(1, $xpath->query('/a:feed/a:subtitle')->length);
        if (null !== $tagValue && 0 === strpos($type, 'tag')) {
            $this->assertEquals('wallabag — '.$type.' '.$tagValue.' feed', $xpath->query('/a:feed/a:title')->item(0)->nodeValue);
            $this->assertEquals('Atom feed for entries tagged with ' . $tagValue, $xpath->query('/a:feed/a:subtitle')->item(0)->nodeValue);
        } else {
            $this->assertEquals('wallabag — '.$type.' feed', $xpath->query('/a:feed/a:title')->item(0)->nodeValue);
            $this->assertEquals('Atom feed for ' . $type . ' entries', $xpath->query('/a:feed/a:subtitle')->item(0)->nodeValue);
        }

        $this->assertEquals(1, $xpath->query('/a:feed/a:link[@rel="self"]')->length);
        $this->assertContains($type, $xpath->query('/a:feed/a:link[@rel="self"]')->item(0)->getAttribute('href'));

        $this->assertEquals(1, $xpath->query('/a:feed/a:link[@rel="last"]')->length);

        foreach ($xpath->query('//a:entry') as $item) {
            $this->assertEquals(1, $xpath->query('a:title', $item)->length);
            $this->assertEquals(1, $xpath->query('a:link[@rel="via"]', $item)->length);
            $this->assertEquals(1, $xpath->query('a:link[@rel="alternate"]', $item)->length);
            $this->assertEquals(1, $xpath->query('a:id', $item)->length);
            $this->assertEquals(1, $xpath->query('a:published', $item)->length);
            $this->assertEquals(1, $xpath->query('a:content', $item)->length);
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
            ->getRepository('WallabagUserBundle:User')
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
            ->getRepository('WallabagUserBundle:User')
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
            ->getRepository('WallabagUserBundle:User')
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
            ->getRepository('WallabagUserBundle:User')
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
            ->getRepository('WallabagUserBundle:User')
            ->findOneByUsername('admin');

        $config = $user->getConfig();
        $config->setFeedToken('SUPERTOKEN');
        $config->setFeedLimit(1);
        $em->persist($config);
        $em->flush();

        $client = $this->getClient();

        $client->request('GET', '/feed/admin/SUPERTOKEN/unread');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->validateDom($client->getResponse()->getContent(), 'unread');

        $client->request('GET', '/feed/admin/SUPERTOKEN/unread/2');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->validateDom($client->getResponse()->getContent(), 'unread');

        $client->request('GET', '/feed/admin/SUPERTOKEN/unread/3000');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testTags()
    {
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('WallabagUserBundle:User')
            ->findOneByUsername('admin');

        $config = $user->getConfig();
        $config->setFeedToken('SUPERTOKEN');
        $config->setFeedLimit(null);
        $em->persist($config);
        $em->flush();

        $client = $this->getClient();
        $client->request('GET', '/admin/SUPERTOKEN/tags/foo-bar.xml');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->validateDom($client->getResponse()->getContent(), 'tag', 2, 'foo-bar');

        $client->request('GET', '/admin/SUPERTOKEN/tags/foo-bar.xml?page=3000');
        $this->assertSame(302, $client->getResponse()->getStatusCode());
    }
}
