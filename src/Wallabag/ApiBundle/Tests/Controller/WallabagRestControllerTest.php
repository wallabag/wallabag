<?php

namespace Wallabag\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WallabagRestControllerTest extends WebTestCase
{
    protected static $salt;

    /**
     * Grab the salt once and store it to be available for all tests.
     */
    public static function setUpBeforeClass()
    {
        $client = self::createClient();

        $user = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:User')
            ->findOneByUsername('admin');

        self::$salt = $user->getSalt();
    }

    /**
     * Generate HTTP headers for authenticate user on API.
     *
     * @param string $username
     * @param string $password
     *
     * @return array
     */
    private function generateHeaders($username, $password)
    {
        $encryptedPassword = sha1($password.$username.self::$salt);
        $nonce = substr(md5(uniqid('nonce_', true)), 0, 16);

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $created = (string) $now->format('Y-m-d\TH:i:s\Z');
        $digest = base64_encode(sha1(base64_decode($nonce).$created.$encryptedPassword, true));

        return array(
            'HTTP_AUTHORIZATION' => 'Authorization profile="UsernameToken"',
            'HTTP_x-wsse' => 'X-WSSE: UsernameToken Username="'.$username.'", PasswordDigest="'.$digest.'", Nonce="'.$nonce.'", Created="'.$created.'"',
        );
    }

    public function testGetSalt()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/salts/admin.json');

        $user = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:User')
            ->findOneByUsername('admin');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey(0, $content);
        $this->assertEquals($user->getSalt(), $content[0]);

        $client->request('GET', '/api/salts/notfound.json');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testWithBadHeaders()
    {
        $client = $this->createClient();

        $entry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByIsArchived(false);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $badHeaders = array(
            'HTTP_AUTHORIZATION' => 'Authorization profile="UsernameToken"',
            'HTTP_x-wsse' => 'X-WSSE: UsernameToken Username="admin", PasswordDigest="Wr0ngDig3st", Nonce="n0Nc3", Created="2015-01-01T13:37:00Z"',
        );

        $client->request('GET', '/api/entries/'.$entry->getId().'.json', array(), array(), $badHeaders);
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testGetOneEntry()
    {
        $client = $this->createClient();
        $headers = $this->generateHeaders('admin', 'mypassword');

        $entry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneBy(array('user' => 1, 'isArchived' => false));

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $client->request('GET', '/api/entries/'.$entry->getId().'.json', array(), array(), $headers);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($entry->getTitle(), $content['title']);
        $this->assertEquals($entry->getUrl(), $content['url']);
        $this->assertCount(count($entry->getTags()), $content['tags']);

        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
    }

    public function testGetOneEntryWrongUser()
    {
        $client = $this->createClient();
        $headers = $this->generateHeaders('admin', 'mypassword');

        $entry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneBy(array('user' => 2, 'isArchived' => false));

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $client->request('GET', '/api/entries/'.$entry->getId().'.json', array(), array(), $headers);

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testGetEntries()
    {
        $client = $this->createClient();
        $headers = $this->generateHeaders('admin', 'mypassword');

        $client->request('GET', '/api/entries', array(), array(), $headers);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, count($content));
        $this->assertNotEmpty($content['_embedded']['items']);
        $this->assertGreaterThanOrEqual(1, $content['total']);
        $this->assertEquals(1, $content['page']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
    }

    public function testGetStarredEntries()
    {
        $client = $this->createClient();
        $headers = $this->generateHeaders('admin', 'mypassword');

        $client->request('GET', '/api/entries', array('archive' => 1), array(), $headers);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertGreaterThanOrEqual(1, count($content));
        $this->assertNotEmpty($content['_embedded']['items']);
        $this->assertGreaterThanOrEqual(1, $content['total']);
        $this->assertEquals(1, $content['page']);
        $this->assertGreaterThanOrEqual(1, $content['pages']);

        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
    }

    public function testDeleteEntry()
    {
        $client = $this->createClient();
        $headers = $this->generateHeaders('admin', 'mypassword');

        $entry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUser(1);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $client->request('DELETE', '/api/entries/'.$entry->getId().'.json', array(), array(), $headers);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($entry->getTitle(), $content['title']);
        $this->assertEquals($entry->getUrl(), $content['url']);

        // We'll try to delete this entry again
        $headers = $this->generateHeaders('admin', 'mypassword');

        $client->request('DELETE', '/api/entries/'.$entry->getId().'.json', array(), array(), $headers);

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testPostEntry()
    {
        $client = $this->createClient();
        $headers = $this->generateHeaders('admin', 'mypassword');

        $client->request('POST', '/api/entries.json', array(
            'url' => 'http://www.lemonde.fr/pixels/article/2015/03/28/plongee-dans-l-univers-d-ingress-le-jeu-de-google-aux-frontieres-du-reel_4601155_4408996.html',
            'tags' => 'google',
        ), array(), $headers);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content['id']);
        $this->assertEquals('http://www.lemonde.fr/pixels/article/2015/03/28/plongee-dans-l-univers-d-ingress-le-jeu-de-google-aux-frontieres-du-reel_4601155_4408996.html', $content['url']);
        $this->assertEquals(false, $content['is_archived']);
        $this->assertEquals(false, $content['is_starred']);
        $this->assertCount(1, $content['tags']);
    }

    public function testPatchEntry()
    {
        $client = $this->createClient();
        $headers = $this->generateHeaders('admin', 'mypassword');

        $entry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUser(1);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        // hydrate the tags relations
        $nbTags = count($entry->getTags());

        $client->request('PATCH', '/api/entries/'.$entry->getId().'.json', array(
            'title' => 'New awesome title',
            'tags' => 'new tag '.uniqid(),
            'star' => true,
            'archive' => false,
        ), array(), $headers);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals($entry->getId(), $content['id']);
        $this->assertEquals($entry->getUrl(), $content['url']);
        $this->assertEquals('New awesome title', $content['title']);
        $this->assertGreaterThan($nbTags, count($content['tags']));
    }

    public function testGetTagsEntry()
    {
        $client = $this->createClient();
        $headers = $this->generateHeaders('admin', 'mypassword');

        $entry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneWithTags(1);

        $entry = $entry[0];

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $tags = array();
        foreach ($entry->getTags() as $tag) {
            $tags[] = array('id' => $tag->getId(), 'label' => $tag->getLabel());
        }

        $client->request('GET', '/api/entries/'.$entry->getId().'/tags', array(), array(), $headers);

        $this->assertEquals(json_encode($tags, JSON_HEX_QUOT), $client->getResponse()->getContent());
    }

    public function testPostTagsOnEntry()
    {
        $client = $this->createClient();
        $headers = $this->generateHeaders('admin', 'mypassword');

        $entry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUser(1);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $nbTags = count($entry->getTags());

        $newTags = 'tag1,tag2,tag3';

        $client->request('POST', '/api/entries/'.$entry->getId().'/tags', array('tags' => $newTags), array(), $headers);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('tags', $content);
        $this->assertEquals($nbTags + 3, count($content['tags']));

        $entryDB = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->find($entry->getId());

        $tagsInDB = array();
        foreach ($entryDB->getTags()->toArray() as $tag) {
            $tagsInDB[$tag->getId()] = $tag->getLabel();
        }

        foreach (explode(',', $newTags) as $tag) {
            $this->assertContains($tag, $tagsInDB);
        }
    }

    public function testDeleteOneTagEntrie()
    {
        $client = $this->createClient();
        $headers = $this->generateHeaders('admin', 'mypassword');

        $entry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUser(1);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        // hydrate the tags relations
        $nbTags = count($entry->getTags());
        $tag = $entry->getTags()[0];

        $client->request('DELETE', '/api/entries/'.$entry->getId().'/tags/'.$tag->getId().'.json', array(), array(), $headers);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('tags', $content);
        $this->assertEquals($nbTags - 1, count($content['tags']));
    }

    public function testGetUserTags()
    {
        $client = $this->createClient();
        $headers = $this->generateHeaders('admin', 'mypassword');

        $client->request('GET', '/api/tags.json', array(), array(), $headers);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertGreaterThan(0, $content);
        $this->assertArrayHasKey('id', $content[0]);
        $this->assertArrayHasKey('label', $content[0]);

        return end($content);
    }

    /**
     * @depends testGetUserTags
     */
    public function testDeleteUserTag($tag)
    {
        $client = $this->createClient();
        $headers = $this->generateHeaders('admin', 'mypassword');

        $client->request('DELETE', '/api/tags/'.$tag['id'].'.json', array(), array(), $headers);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('label', $content);
        $this->assertEquals($tag['label'], $content['label']);
    }
}
