<?php

namespace Tests\Wallabag\CoreBundle\Controller;

use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;

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

        $data = [
            'config[theme]' => 'baggy',
            'config[items_per_page]' => '30',
            'config[reading_speed]' => '0.5',
            'config[language]' => 'en',
        ];

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $alert = $crawler->filter('div.messages.success')->extract(['_text']));
        $this->assertContains('flashes.config.notice.config_saved', $alert[0]);
    }

    public function testChangeReadingSpeed()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/unread/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();
        $dataFilters = [
            'entry_filter[readingTime][right_number]' => 22,
            'entry_filter[readingTime][left_number]' => 22,
        ];
        $crawler = $client->submit($form, $dataFilters);
        $this->assertCount(1, $crawler->filter('div[class=entry]'));

        // Change reading speed
        $crawler = $client->request('GET', '/config');
        $form = $crawler->filter('button[id=config_save]')->form();
        $data = [
            'config[reading_speed]' => '2',
        ];
        $client->submit($form, $data);

        // Is the entry still available via filters?
        $crawler = $client->request('GET', '/unread/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();
        $crawler = $client->submit($form, $dataFilters);
        $this->assertCount(0, $crawler->filter('div[class=entry]'));

        // Restore old configuration
        $crawler = $client->request('GET', '/config');
        $form = $crawler->filter('button[id=config_save]')->form();
        $data = [
            'config[reading_speed]' => '0.5',
        ];
        $client->submit($form, $data);
    }

    public function dataForUpdateFailed()
    {
        return [
            [[
                'config[theme]' => 'baggy',
                'config[items_per_page]' => '',
                'config[language]' => 'en',
            ]],
        ];
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

        $this->assertGreaterThan(1, $alert = $crawler->filter('body')->extract(['_text']));
        $this->assertContains('This value should not be blank', $alert[0]);
    }

    public function dataForChangePasswordFailed()
    {
        return [
            [
                [
                    'change_passwd[old_password]' => 'material',
                    'change_passwd[new_password][first]' => '',
                    'change_passwd[new_password][second]' => '',
                ],
                'validator.password_wrong_value',
            ],
            [
                [
                    'change_passwd[old_password]' => 'mypassword',
                    'change_passwd[new_password][first]' => '',
                    'change_passwd[new_password][second]' => '',
                ],
                'This value should not be blank',
            ],
            [
                [
                    'change_passwd[old_password]' => 'mypassword',
                    'change_passwd[new_password][first]' => 'hop',
                    'change_passwd[new_password][second]' => '',
                ],
                'validator.password_must_match',
            ],
            [
                [
                    'change_passwd[old_password]' => 'mypassword',
                    'change_passwd[new_password][first]' => 'hop',
                    'change_passwd[new_password][second]' => 'hop',
                ],
                'validator.password_too_short',
            ],
        ];
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

        $this->assertGreaterThan(1, $alert = $crawler->filter('body')->extract(['_text']));
        $this->assertContains($expectedMessage, $alert[0]);
    }

    public function testChangePassword()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/config');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=change_passwd_save]')->form();

        $data = [
            'change_passwd[old_password]' => 'mypassword',
            'change_passwd[new_password][first]' => 'mypassword',
            'change_passwd[new_password][second]' => 'mypassword',
        ];

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $alert = $crawler->filter('div.messages.success')->extract(['_text']));
        $this->assertContains('flashes.config.notice.password_updated', $alert[0]);
    }

    public function dataForUserFailed()
    {
        return [
            [
                [
                    'update_user[name]' => '',
                    'update_user[email]' => '',
                ],
                'fos_user.email.blank',
            ],
            [
                [
                    'update_user[name]' => '',
                    'update_user[email]' => 'test',
                ],
                'fos_user.email.invalid',
            ],
        ];
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

        $this->assertGreaterThan(1, $alert = $crawler->filter('body')->extract(['_text']));
        $this->assertContains($expectedMessage, $alert[0]);
    }

    public function testUserUpdate()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/config');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=update_user_save]')->form();

        $data = [
            'update_user[name]' => 'new name',
            'update_user[email]' => 'admin@wallabag.io',
        ];

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $alert = $crawler->filter('body')->extract(['_text']));
        $this->assertContains('flashes.config.notice.user_updated', $alert[0]);
    }

    public function dataForNewUserFailed()
    {
        return [
            [
                [
                    'new_user[username]' => '',
                    'new_user[plainPassword][first]' => '',
                    'new_user[plainPassword][second]' => '',
                    'new_user[email]' => '',
                ],
                'fos_user.username.blank',
            ],
            [
                [
                    'new_user[username]' => 'a',
                    'new_user[plainPassword][first]' => 'mypassword',
                    'new_user[plainPassword][second]' => 'mypassword',
                    'new_user[email]' => '',
                ],
                'fos_user.username.short',
            ],
            [
                [
                    'new_user[username]' => 'wallace',
                    'new_user[plainPassword][first]' => 'mypassword',
                    'new_user[plainPassword][second]' => 'mypassword',
                    'new_user[email]' => 'test',
                ],
                'fos_user.email.invalid',
            ],
            [
                [
                    'new_user[username]' => 'admin',
                    'new_user[plainPassword][first]' => 'wallacewallace',
                    'new_user[plainPassword][second]' => 'wallacewallace',
                    'new_user[email]' => 'wallace@wallace.me',
                ],
                'fos_user.username.already_used',
            ],
            [
                [
                    'new_user[username]' => 'wallace',
                    'new_user[plainPassword][first]' => 'mypassword1',
                    'new_user[plainPassword][second]' => 'mypassword2',
                    'new_user[email]' => 'wallace@wallace.me',
                ],
                'validator.password_must_match',
            ],
        ];
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

        $this->assertGreaterThan(1, $alert = $crawler->filter('body')->extract(['_text']));
        $this->assertContains($expectedMessage, $alert[0]);
    }

    public function testNewUserCreated()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/config');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=new_user_save]')->form();

        $data = [
            'new_user[username]' => 'wallace',
            'new_user[plainPassword][first]' => 'wallace1',
            'new_user[plainPassword][second]' => 'wallace1',
            'new_user[email]' => 'wallace@wallace.me',
        ];

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $alert = $crawler->filter('div.messages.success')->extract(['_text']));
        $this->assertContains('flashes.config.notice.user_added', $alert[0]);

        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('WallabagUserBundle:User')
            ->findOneByUsername('wallace');

        $this->assertTrue(false !== $user);
        $this->assertTrue($user->isEnabled());
        $this->assertEquals('material', $user->getConfig()->getTheme());
        $this->assertEquals(12, $user->getConfig()->getItemsPerPage());
        $this->assertEquals(50, $user->getConfig()->getRssLimit());
        $this->assertEquals('en', $user->getConfig()->getLanguage());
        $this->assertEquals(1, $user->getConfig()->getReadingSpeed());
    }

    public function testRssUpdateResetToken()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        // reset the token
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $user = $em
            ->getRepository('WallabagUserBundle:User')
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

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertContains('config.form_rss.no_token', $body[0]);

        $client->request('GET', '/generate-token');
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertNotContains('config.form_rss.no_token', $body[0]);
    }

    public function testGenerateTokenAjax()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->request(
            'GET',
            '/generate-token',
            [],
            [],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
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

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=rss_config_save]')->form();

        $data = [
            'rss_config[rss_limit]' => 12,
        ];

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $alert = $crawler->filter('div.messages.success')->extract(['_text']));
        $this->assertContains('flashes.config.notice.rss_updated', $alert[0]);
    }

    public function dataForRssFailed()
    {
        return [
            [
                [
                    'rss_config[rss_limit]' => 0,
                ],
                'This value should be 1 or more.',
            ],
            [
                [
                    'rss_config[rss_limit]' => 1000000000000,
                ],
                'validator.rss_limit_too_hight',
            ],
        ];
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

        $this->assertGreaterThan(1, $alert = $crawler->filter('body')->extract(['_text']));
        $this->assertContains($expectedMessage, $alert[0]);
    }

    public function testTaggingRuleCreation()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/config');

        $this->assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->filter('button[id=tagging_rule_save]')->form();

        $data = [
            'tagging_rule[rule]' => 'readingTime <= 3',
            'tagging_rule[tags]' => 'short reading',
        ];

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $alert = $crawler->filter('div.messages.success')->extract(['_text']));
        $this->assertContains('flashes.config.notice.tagging_rules_updated', $alert[0]);

        $deleteLink = $crawler->filter('.delete')->last()->link();

        $crawler = $client->click($deleteLink);
        $this->assertEquals(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();
        $this->assertGreaterThan(1, $alert = $crawler->filter('div.messages.success')->extract(['_text']));
        $this->assertContains('flashes.config.notice.tagging_rules_deleted', $alert[0]);
    }

    public function dataForTaggingRuleFailed()
    {
        return [
            [
                [
                    'tagging_rule[rule]' => 'unknownVar <= 3',
                    'tagging_rule[tags]' => 'cool tag',
                ],
                [
                    'The variable',
                    'does not exist.',
                ],
            ],
            [
                [
                    'tagging_rule[rule]' => 'length(domainName) <= 42',
                    'tagging_rule[tags]' => 'cool tag',
                ],
                [
                    'The operator',
                    'does not exist.',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataForTaggingRuleFailed
     */
    public function testTaggingRuleCreationFail($data, $messages)
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/config');

        $this->assertTrue($client->getResponse()->isSuccessful());

        $form = $crawler->filter('button[id=tagging_rule_save]')->form();

        $crawler = $client->submit($form, $data);

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));

        foreach ($messages as $message) {
            $this->assertContains($message, $body[0]);
        }
    }

    public function testDeletingTaggingRuleFromAnOtherUser()
    {
        $this->logInAs('bob');
        $client = $this->getClient();

        $rule = $client->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:TaggingRule')
            ->findAll()[0];

        $crawler = $client->request('GET', '/tagging-rule/delete/'.$rule->getId());

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertContains('You can not access this tagging rule', $body[0]);
    }

    public function testDemoMode()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $config = $client->getContainer()->get('craue_config');
        $config->set('demo_mode_enabled', 1);
        $config->set('demo_mode_username', 'admin');

        $crawler = $client->request('GET', '/config');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=change_passwd_save]')->form();

        $data = [
            'change_passwd[old_password]' => 'mypassword',
            'change_passwd[new_password][first]' => 'mypassword',
            'change_passwd[new_password][second]' => 'mypassword',
        ];

        $client->submit($form, $data);

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertContains('flashes.config.notice.password_not_updated_demo', $client->getContainer()->get('session')->getFlashBag()->get('notice')[0]);

        $config->set('demo_mode_enabled', 0);
        $config->set('demo_mode_username', 'wallabag');
    }
}
