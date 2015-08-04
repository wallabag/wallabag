<?php

namespace Wallabag\CoreBundle\Tests\Controller;

use Wallabag\CoreBundle\Tests\WallabagCoreTestCase;

class ConfigControllerTest extends WallabagCoreTestCase
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
        $this->assertCount(1, $crawler->filter('button[id=update_user_save]'));
        $this->assertCount(1, $crawler->filter('button[id=new_user_save]'));
        $this->assertCount(1, $crawler->filter('button[id=rss_config_save]'));
    }

    public function testUpdate()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/config');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=config_save]')->form();

        $data = array(
            'config[theme]' => 0,
            'config[items_per_page]' => '30',
            'config[language]' => 'fr_FR',
        );

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $alert = $crawler->filter('div.messages.success')->extract(array('_text')));
        $this->assertContains('Config saved', $alert[0]);
    }

    public function dataForUpdateFailed()
    {
        return array(
            array(array(
                'config[theme]' => 0,
                'config[items_per_page]' => '',
                'config[language]' => 'fr_FR',
            )),
            array(array(
                'config[theme]' => 0,
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
                    'change_passwd[old_password]' => 'material',
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
                'Password should by at least',
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

        $this->assertGreaterThan(1, $alert = $crawler->filter('div.messages.success')->extract(array('_text')));
        $this->assertContains('Password updated', $alert[0]);
    }

    public function dataForUserFailed()
    {
        return array(
            array(
                array(
                    'update_user[name]' => '',
                    'update_user[email]' => '',
                ),
                'This value should not be blank.',
            ),
            array(
                array(
                    'update_user[name]' => '',
                    'update_user[email]' => 'test',
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

        $form = $crawler->filter('button[id=update_user_save]')->form();

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

        $form = $crawler->filter('button[id=update_user_save]')->form();

        $data = array(
            'update_user[name]' => 'new name',
            'update_user[email]' => 'admin@wallabag.io',
        );

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $alert = $crawler->filter('div.messages.success')->extract(array('_text')));
        $this->assertContains('Information updated', $alert[0]);
    }

    public function dataForNewUserFailed()
    {
        return array(
            array(
                array(
                    'new_user[username]' => '',
                    'new_user[password]' => '',
                    'new_user[email]' => '',
                ),
                'This value should not be blank.',
            ),
            array(
                array(
                    'new_user[username]' => 'ad',
                    'new_user[password]' => '',
                    'new_user[email]' => '',
                ),
                'This value is too short.',
            ),
            array(
                array(
                    'new_user[username]' => 'wallace',
                    'new_user[password]' => '',
                    'new_user[email]' => 'test',
                ),
                'This value is not a valid email address.',
            ),
            array(
                array(
                    'new_user[username]' => 'wallace',
                    'new_user[password]' => 'admin',
                    'new_user[email]' => 'wallace@wallace.me',
                ),
                'Password should by at least',
            ),
            array(
                array(
                    'new_user[username]' => 'admin',
                    'new_user[password]' => 'wallacewallace',
                    'new_user[email]' => 'wallace@wallace.me',
                ),
                'This value is already used',
            ),
        );
    }

    /**
     * @dataProvider dataForNewUserFailed
     */
    public function testNewUserFailed($data, $expectedMessage)
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/config');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=new_user_save]')->form();

        $crawler = $client->submit($form, $data);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(1, $alert = $crawler->filter('body')->extract(array('_text')));
        $this->assertContains($expectedMessage, $alert[0]);
    }

    public function testNewUserCreated()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/config');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=new_user_save]')->form();

        $data = array(
            'new_user[username]' => 'wallace',
            'new_user[password]' => 'wallace1',
            'new_user[email]' => 'wallace@wallace.me',
        );

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $alert = $crawler->filter('div.messages.success')->extract(array('_text')));
        $this->assertContains('User "wallace" added', $alert[0]);
    }

    public function testRssUpdateResetToken()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        // reset the token
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('WallabagCoreBundle:User')
            ->findOneByUsername('admin');

        if (!$user) {
            $this->markTestSkipped('No user found in db.');
        }

        $config = $user->getConfig();
        $config->setRssToken(null);
        $em->persist($config);
        $em->flush();

        $crawler = $client->request('GET', '/config');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(array('_text')));
        $this->assertContains('You need to generate a token first.', $body[0]);

        $client->request('GET', '/generate-token');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(array('_text')));
        $this->assertNotContains('You need to generate a token first.', $body[0]);
    }

    public function testGenerateTokenAjax()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request(
            'GET',
            '/generate-token',
            array(),
            array(),
            array('HTTP_X-Requested-With' => 'XMLHttpRequest')
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $content = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $content);
    }

    public function testRssUpdate()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/config');

        if (500 == $client->getResponse()->getStatusCode()) {
            var_export($client->getResponse()->getContent());
            die();
        }

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=rss_config_save]')->form();

        $data = array(
            'rss_config[rss_limit]' => 12,
        );

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $alert = $crawler->filter('div.messages.success')->extract(array('_text')));
        $this->assertContains('RSS information updated', $alert[0]);
    }

    public function dataForRssFailed()
    {
        return array(
            array(
                array(
                    'rss_config[rss_limit]' => 0,
                ),
                'This value should be 1 or more.',
            ),
            array(
                array(
                    'rss_config[rss_limit]' => 1000000000000,
                ),
                'This will certainly kill the app',
            ),
        );
    }

    /**
     * @dataProvider dataForRssFailed
     */
    public function testRssFailed($data, $expectedMessage)
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/config');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=rss_config_save]')->form();

        $crawler = $client->submit($form, $data);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(1, $alert = $crawler->filter('body')->extract(array('_text')));
        $this->assertContains($expectedMessage, $alert[0]);
    }
}
