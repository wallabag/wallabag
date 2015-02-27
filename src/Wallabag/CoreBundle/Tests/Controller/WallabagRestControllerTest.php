<?php

namespace Wallabag\CoreBundle\Tests\Controller;

use Wallabag\CoreBundle\Tests\WallabagTestCase;

class WallabagRestControllerTest extends WallabagTestCase
{
    /**
     * Generate HTTP headers for authenticate user on API
     *
     * @param $username
     * @param $password
     * @param $salt
     *
     * @return array
     */
    private function generateHeaders($username, $password, $salt)
    {
        $encryptedPassword = sha1($password.$username.$salt);
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
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertNotEmpty(json_decode($client->getResponse()->getContent()));

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
        $client->request('GET', '/api/salts/admin.json');
        $salt = json_decode($client->getResponse()->getContent());

        $headers = $this->generateHeaders('admin', 'mypassword', $salt[0]);

        $entry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByIsArchived(false);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $client->request('GET', '/api/entries/'.$entry->getId().'.json', array(), array(), $headers);
        $this->assertContains($entry->getTitle(), $client->getResponse()->getContent());

        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
    }

    public function testGetEntries()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/salts/admin.json');
        $salt = json_decode($client->getResponse()->getContent());

        $headers = $this->generateHeaders('admin', 'mypassword', $salt[0]);

        $client->request('GET', '/api/entries', array(), array(), $headers);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThanOrEqual(1, count(json_decode($client->getResponse()->getContent())));

        $this->assertContains('Mailjet', $client->getResponse()->getContent());

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
        $client->request('GET', '/api/salts/admin.json');
        $salt = json_decode($client->getResponse()->getContent());

        $headers = $this->generateHeaders('admin', 'mypassword', $salt[0]);

        $entry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUser(1);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $client->request('DELETE', '/api/entries/'.$entry->getId().'.json', array(), array(), $headers);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // We'll try to delete this entry again
        $client->request('GET', '/api/salts/admin.json');
        $salt = json_decode($client->getResponse()->getContent());

        $headers = $this->generateHeaders('admin', 'test', $salt[0]);

        $client->request('DELETE', '/api/entries/'.$entry->getId().'.json', array(), array(), $headers);

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testGetTagsEntry()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/salts/admin.json');
        $salt = json_decode($client->getResponse()->getContent());
        $headers = $this->generateHeaders('admin', 'test', $salt[0]);

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
        $client->request('GET', '/api/salts/admin.json');
        $salt = json_decode($client->getResponse()->getContent());
        $headers = $this->generateHeaders('admin', 'test', $salt[0]);

        $entry = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findOneByUser(1);

        if (!$entry) {
            $this->markTestSkipped('No content found in db.');
        }

        $newTags = 'tag1,tag2,tag3';

        $client->request('POST', '/api/entries/'.$entry->getId().'/tags', array('tags' => $newTags), array(), $headers);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

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
}
