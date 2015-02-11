<?php

namespace Wallabag\CoreBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WallabagTestCase extends WebTestCase
{
    private $client = null;

    public function getClient()
    {
        return $this->client;
    }

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function logInAs($username)
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->filter('button[type=submit]')->form();
        $data = array(
            '_username' => $username,
            '_password' => 'test',
        );

        $this->client->submit($form, $data);
    }
}
