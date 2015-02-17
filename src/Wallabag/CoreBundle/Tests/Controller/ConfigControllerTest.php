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

        $this->assertCount(1, $crawler->filter('button[id=config_save]'));
        $this->assertCount(1, $crawler->filter('button[id=change_passwd_save]'));
        $this->assertCount(1, $crawler->filter('button[id=user_save]'));
    }

    public function testUpdate()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/config');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=config_save]')->form();

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

        $form = $crawler->filter('button[id=config_save]')->form();

        $crawler = $client->submit($form, $data);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(1, $alert = $crawler->filter('body')->extract(array('_text')));
        $this->assertContains('This value should not be blank', $alert[0]);
    }

    public function dataForChangePasswordFailed()
    {
        return array(
            array(
                array(
                    'change_passwd[old_password]' => 'baggy',
                    'change_passwd[new_password][first]' => '',
                    'change_passwd[new_password][second]' => '',
                ),
                'Wrong value for your current password',
            ),
            array(
                array(
                    'change_passwd[old_password]' => 'mypassword',
                    'change_passwd[new_password][first]' => '',
                    'change_passwd[new_password][second]' => '',
                ),
                'This value should not be blank',
            ),
            array(
                array(
                    'change_passwd[old_password]' => 'mypassword',
                    'change_passwd[new_password][first]' => 'hop',
                    'change_passwd[new_password][second]' => '',
                ),
                'The password fields must match',
            ),
            array(
                array(
                    'change_passwd[old_password]' => 'mypassword',
                    'change_passwd[new_password][first]' => 'hop',
                    'change_passwd[new_password][second]' => 'hop',
                ),
                'Password should by at least 6 chars long',
            ),
        );
    }

    /**
     * @dataProvider dataForChangePasswordFailed
     */
    public function testChangePasswordFailed($data, $expectedMessage)
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/config');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=change_passwd_save]')->form();

        $crawler = $client->submit($form, $data);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(1, $alert = $crawler->filter('body')->extract(array('_text')));
        $this->assertContains($expectedMessage, $alert[0]);
    }

    public function testChangePassword()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/config');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=change_passwd_save]')->form();

        $data = array(
            'change_passwd[old_password]' => 'mypassword',
            'change_passwd[new_password][first]' => 'mypassword',
            'change_passwd[new_password][second]' => 'mypassword',
        );

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $alert = $crawler->filter('div.flash-notice')->extract(array('_text')));
        $this->assertContains('Password updated', $alert[0]);
    }

    public function dataForUserFailed()
    {
        return array(
            array(
                array(
                    'user[username]' => '',
                    'user[name]' => '',
                    'user[email]' => '',
                ),
                'This value should not be blank.',
            ),
            array(
                array(
                    'user[username]' => 'ad',
                    'user[name]' => '',
                    'user[email]' => '',
                ),
                'This value is too short.',
            ),
            array(
                array(
                    'user[username]' => 'admin',
                    'user[name]' => '',
                    'user[email]' => 'test',
                ),
                'This value is not a valid email address.',
            ),
        );
    }

    /**
     * @dataProvider dataForUserFailed
     */
    public function testUserFailed($data, $expectedMessage)
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/config');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=user_save]')->form();

        $crawler = $client->submit($form, $data);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(1, $alert = $crawler->filter('body')->extract(array('_text')));
        $this->assertContains($expectedMessage, $alert[0]);
    }

    public function testUserUpdate()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/config');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=user_save]')->form();

        $data = array(
            'user[username]' => 'admin',
            'user[name]' => 'new name',
            'user[email]' => 'admin@wallabag.io',
        );

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $alert = $crawler->filter('div.flash-notice')->extract(array('_text')));
        $this->assertContains('Information updated', $alert[0]);
    }
}
