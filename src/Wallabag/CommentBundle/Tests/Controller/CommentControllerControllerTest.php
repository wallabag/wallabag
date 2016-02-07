<?php

namespace Wallabag\CommentBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CommentControllerControllerTest extends WebTestCase
{
    public function testGetcomment()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/annotations');
    }

    public function testSetcomment()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/annotations');
    }

    public function testEditcomment()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/annotations');
    }

}
