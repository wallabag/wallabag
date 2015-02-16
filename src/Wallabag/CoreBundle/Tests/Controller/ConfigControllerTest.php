<?php

namespace Wallabag\CoreBundle\Tests\Controller;

use Wallabag\CoreBundle\Tests\WallabagTestCase;

class ConfigControllerTest extends WallabagTestCase
{
    public function testLogin()
    {
        $client = $this->getClient();

        $client->request('GET', '/new');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertContains('login', $client->getResponse()->headers->get('location'));
    }

    public function testIndex()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/config');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertCount(1, $crawler->filter('input[type=number]'));
        $this->assertCount(1, $crawler->filter('button[type=submit]'));
    }

    public function testUpdate()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/config');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[type=submit]')->form();

        $data = array(
            'config[theme]' => 'baggy',
            'config[items_per_page]' => '30',
            'config[language]' => 'fr_FR',
        );

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $alert = $crawler->filter('div.flash-notice')->extract(array('_text')));
        $this->assertContains('Config saved', $alert[0]);
    }

    public function dataForUpdateFailed()
    {
        return array(
            array(array(
                'config[theme]' => 'baggy',
                'config[items_per_page]' => '',
                'config[language]' => 'fr_FR',
            )),
            array(array(
                'config[theme]' => 'baggy',
                'config[items_per_page]' => '12',
                'config[language]' => '',
            )),
        );
    }

    /**
     * @dataProvider dataForUpdateFailed
     */
    public function testUpdateFailed($data)
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/config');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[type=submit]')->form();

        $crawler = $client->submit($form, $data);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(1, $alert = $crawler->filter('body')->extract(array('_text')));
        $this->assertContains('This value should not be blank', $alert[0]);
    }
}
