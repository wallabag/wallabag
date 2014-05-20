<?php
namespace Wallabag\Tests\Functionals;
use Wallabag\Tests\Functionals\Fixtures;

class ApiTest extends WallabagWebTestCase
{
    public function testEmptyGetEntries()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/api/entries');

        $this->assertTrue($client->getResponse()->isOk());

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        $this->assertEquals('[]', $client->getResponse()->getContent());

    }

    public function testGetEntries()
    {

        //Load some entries
        Fixtures::loadEntries($this->app['db']);

        $client = $this->createClient();
        $crawler = $client->request('GET', '/api/entries');

        $this->assertTrue($client->getResponse()->isOk());

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        $this->assertEquals('[{"id":"1","url":"http:\/\/deboutlesgens.com\/blog\/le-courage-de-vivre-consciemment\/","title":"Le courage de vivre consciemment","content":"Test content","updated":null,"status":"unread","bookmark":"0","fetched":"1","user_id":"1"}]', $client->getResponse()->getContent());

    }

    public function testGetBookmarks()
    {

        //Load some entries
        Fixtures::loadEntries($this->app['db']);

        $client = $this->createClient();

        $crawler = $client->request(
            'GET',
            '/api/star',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"id":"1"}'
        );

        $this->assertEquals($client->getResponse()->getStatusCode(), 201);

        $crawler = $client->request('GET', '/api/bookmarks');

        $this->assertTrue($client->getResponse()->isOk());

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        $this->assertEquals('[{"id":"1","url":"http:\/\/deboutlesgens.com\/blog\/le-courage-de-vivre-consciemment\/","title":"Le courage de vivre consciemment","content":"Test content","updated":null,"status":"unread","bookmark":"1","fetched":"1","user_id":"1"}]', $client->getResponse()->getContent());

    }

    public function testGetEntryById()
    {

        //Load some entries
        Fixtures::loadEntries($this->app['db']);

        $client = $this->createClient();
        $crawler = $client->request(
            'GET',
            '/api/get',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"id":"1"}'
        );

        $this->assertEquals($client->getResponse()->getStatusCode(), 201);

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        $this->assertEquals('[{"id":"1","url":"http:\/\/deboutlesgens.com\/blog\/le-courage-de-vivre-consciemment\/","title":"Le courage de vivre consciemment","content":"Test content","updated":null,"status":"unread","bookmark":"0","fetched":"1","user_id":"1"}]', $client->getResponse()->getContent());

    }

    public function testMarkAsRead()
    {

        //Load some entries
        Fixtures::loadEntries($this->app['db']);

        $client = $this->createClient();
        $crawler = $client->request(
            'GET',
            '/api/mark-read',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"id":"1"}'
        );

        $this->assertEquals($client->getResponse()->getStatusCode(), 201);

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        $this->assertEquals('1', $client->getResponse()->getContent());

    }

    public function testMarkAsUnread()
    {

        //Load some entries
        Fixtures::loadEntries($this->app['db']);

        $client = $this->createClient();
        $crawler = $client->request(
            'GET',
            '/api/mark-unread',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"id":"1"}'
        );

        $this->assertEquals($client->getResponse()->getStatusCode(), 201);

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        $this->assertEquals('1', $client->getResponse()->getContent());

    }

    public function testStar()
    {

        //Load some entries
        Fixtures::loadEntries($this->app['db']);

        $client = $this->createClient();
        $crawler = $client->request(
            'GET',
            '/api/star',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"id":"1"}'
        );

        $this->assertEquals($client->getResponse()->getStatusCode(), 201);

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        $this->assertEquals('1', $client->getResponse()->getContent());

    }

    public function testUnstar()
    {

        //Load some entries
        Fixtures::loadEntries($this->app['db']);

        $client = $this->createClient();
        $crawler = $client->request(
            'GET',
            '/api/unstar',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"id":"1"}'
        );

        $this->assertEquals($client->getResponse()->getStatusCode(), 201);

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        $this->assertEquals('1', $client->getResponse()->getContent());

    }

    public function testRemove()
    {

        //Load some entries
        Fixtures::loadEntries($this->app['db']);

        $client = $this->createClient();
        $crawler = $client->request(
            'GET',
            '/api/remove',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"id":"1"}'
        );

        $this->assertEquals($client->getResponse()->getStatusCode(), 201);

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        $this->assertEquals('1', $client->getResponse()->getContent());

    }

    public function testPostEntries()
    {

        //Load some entries
        Fixtures::loadEntries($this->app['db']);

        $client = $this->createClient();
        $crawler = $client->request(
            'POST',
            '/api/add',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"url":"http:\/\/perdu.com"}'
        );

        $this->assertEquals($client->getResponse()->getStatusCode(), 201);

        // Assert that the "Content-Type" header is "application/json"
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        $this->assertEquals($client->getResponse()->getContent(),'{"url":"http:\/\/perdu.com","title":"Vous Etes Perdu ?","content":"[unable to retrieve full-text content]"}');
    }
}
