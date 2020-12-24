<?php

namespace Tests\Wallabag\CoreBundle\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;

class FeedControllerTest extends WallabagCoreTestCase
{
    public function validateDom($xml, $type, $nb = null, $tagValue = null, $feedUseSource = false)
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
            if ($feedUseSource) {
                $this->assertSame(0, $xpath->query('a:link[@rel="via"]', $item)->length);
                $this->assertSame(1, $xpath->query('a:link[@rel="alternate"]', $item)->length);
            } else {
                $this->assertSame(1, $xpath->query('a:link[@rel="via"]', $item)->length);
                $this->assertSame(1, $xpath->query('a:link[@rel="alternate"]', $item)->length);
            }
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

    /**
     * @dataProvider dataTestStarred
     */
    public function testStarred($feedUseSource)
    {
        $client = $this->getClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('WallabagUserBundle:User')
            ->findOneByUsername('admin');

        $config = $user->getConfig();
        $config->setFeedToken('SUPERTOKEN');
        $config->setFeedLimit(1);
        $config->setFeedUseSource($feedUseSource);
        $em->persist($config);
        $em->flush();

        $client = $this->getClient();
        $client->request('GET', '/feed/admin/SUPERTOKEN/starred');

        $this->assertSame(200, $client->getResponse()->getStatusCode(), 1);

        $this->validateDom($client->getResponse()->getContent(), 'starred', null, null, $feedUseSource);
    }

    public function dataTestStarred()
    {
        return [
            [true],
            [false],
        ];
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
            ->getRepository('WallabagUserBundle:User')
            ->findOneByUsername('admin');

        $config = $user->getConfig();
        $config->setFeedToken('SUPERTOKEN');
        $config->setFeedLimit(null);
        $em->persist($config);
        $em->flush();

        $client = $this->getClient();
        $client->request('GET', '/feed/admin/SUPERTOKEN/tags/foo');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->validateDom($client->getResponse()->getContent(), 'tag', 2, 'foo');

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
