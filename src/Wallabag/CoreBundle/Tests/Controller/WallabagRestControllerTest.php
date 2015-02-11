<?php

namespace Wallabag\CoreBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WallabagRestControllerTest extends WebTestCase
{
    public function testGetSalt()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/salts/admin.json');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/salts/notfound.json');
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testGetEntries()
    {
        $client = $this->createClient();
        $client->request('GET', '/api/salts/admin.json');
        $content = json_decode($client->getResponse()->getContent());
        $salt = $content[0];

        $username = 'admin';
        $password = 'test';

        $encryptedPassword = sha1($password.$username.$salt);
        $nonce = substr(md5(uniqid('nonce_', true)), 0, 16);

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $created = (string) $now->format('Y-m-d\TH:i:s\Z');
        $digest = base64_encode(sha1(base64_decode($nonce).$created.$encryptedPassword, true));

        $headers = array(
            'PHP_AUTH_USER' => 'username',
            'HTTP_AUTHORIZATION' => 'Authorization profile="UsernameToken"',
            'HTTP_x-wsse' => 'X-WSSE: UsernameToken Username="'.$username.'", PasswordDigest="'.$digest.'", Nonce="'.$nonce.'", Created="'.$created.'"',
        );

        $client->request('GET', '/api/entries', array(), array(), $headers);
        $this->assertContains('Mailjet', $client->getResponse()->getContent());

        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );
    }
}
