<?php

namespace Tests\Wallabag\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\Annotation;
use Wallabag\Entity\Config as ConfigEntity;
use Wallabag\Entity\Entry;
use Wallabag\Entity\IgnoreOriginUserRule;
use Wallabag\Entity\Tag;
use Wallabag\Entity\TaggingRule;
use Wallabag\Entity\User;

class ConfigControllerTest extends WallabagTestCase
{
    public function testLogin()
    {
        $client = $this->getTestClient();

        $client->request('GET', '/new');

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('login', $client->getResponse()->headers->get('location'));
    }

    public function testIndex()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/config');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertCount(1, $crawler->filter('button[id=config_save]'));
        $this->assertCount(1, $crawler->filter('button[id=change_passwd_save]'));
        $this->assertCount(1, $crawler->filter('button[id=update_user_save]'));
        $this->assertCount(1, $crawler->filter('button[id=feed_config_save]'));
    }

    public function testUpdate()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/config');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=config_save]')->form();

        $data = [
            'config[items_per_page]' => '30',
            'config[reading_speed]' => '100',
            'config[action_mark_as_read]' => '0',
            'config[language]' => 'en',
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('flashes.config.notice.config_saved', $crawler->filter('body')->extract(['_text'])[0]);
    }

    public function testChangeReadingSpeed()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $entry = new Entry($this->getLoggedInUser());
        $entry->setUrl('http://0.0.0.0/test-entry1')
            ->setReadingTime(22);
        $this->getEntityManager()->persist($entry);

        $this->getEntityManager()->flush();

        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/unread/list');
        $form = $crawler->filter('button[id=submit-filter]')->form();
        $dataFilters = [
            'entry_filter[readingTime][right_number]' => 22,
            'entry_filter[readingTime][left_number]' => 22,
        ];
        $crawler = $client->submit($form, $dataFilters);
        $this->assertCount(0, $crawler->filter('div[class=entry]'));

        // Change reading speed
        $crawler = $client->request('GET', '/config');
        $form = $crawler->filter('button[id=config_save]')->form();
        $data = [
            'config[reading_speed]' => '400',
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
            'config[reading_speed]' => '100',
        ];
        $client->submit($form, $data);
    }

    public function dataForUpdateFailed()
    {
        return [
            [[
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
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/config');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=config_save]')->form();

        $crawler = $client->submit($form, $data);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(1, $alert = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('This value should not be blank', $alert[0]);
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
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/config');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=change_passwd_save]')->form();

        $crawler = $client->submit($form, $data);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(1, $alert = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString($expectedMessage, $alert[0]);
    }

    public function testChangePassword()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/config');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=change_passwd_save]')->form();

        $data = [
            'change_passwd[old_password]' => 'mypassword',
            'change_passwd[new_password][first]' => 'mypassword',
            'change_passwd[new_password][second]' => 'mypassword',
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('flashes.config.notice.password_updated', $crawler->filter('body')->extract(['_text'])[0]);
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
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/config');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=update_user_save]')->form();

        $crawler = $client->submit($form, $data);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(1, $alert = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString($expectedMessage, $alert[0]);
    }

    public function testUserUpdate()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/config');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=update_user_save]')->form();

        $data = [
            'update_user[name]' => 'new name',
            'update_user[email]' => 'admin@wallabag.io',
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $alert = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.config.notice.user_updated', $alert[0]);
    }

    public function testFeedUpdateResetToken()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        // reset the token
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');

        if (!$user) {
            $this->markTestSkipped('No user found in db.');
        }

        $config = $user->getConfig();
        $config->setFeedToken(null);
        $em->persist($config);
        $em->flush();

        $crawler = $client->request('GET', '/config');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('config.form_feed.no_token', $body[0]);

        $client->submit($crawler->selectButton('config.form_feed.token_create')->form());

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('config.form_feed.token_reset', $body[0]);
    }

    public function testRevokeTokenAjax()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        // set the token
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');

        if (!$user) {
            $this->markTestSkipped('No user found in db.');
        }

        $config = $user->getConfig();
        $config->setFeedToken('abcd1234');
        $em->persist($config);
        $em->flush();

        $crawler = $client->request('GET', '/config');

        $client->submit($crawler->selectButton('config.form_feed.token_revoke')->form());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('config.form_feed.token_create', $body[0]);
    }

    public function testFeedUpdate()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/config');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=feed_config_save]')->form();

        $data = [
            'feed_config[feed_limit]' => 12,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('flashes.config.notice.feed_updated', $crawler->filter('body')->extract(['_text'])[0]);
    }

    public function dataForFeedFailed()
    {
        return [
            [
                [
                    'feed_config[feed_limit]' => 0,
                ],
                'This value should be between 1 and 100000.',
            ],
            [
                [
                    'feed_config[feed_limit]' => 1000000000000,
                ],
                'validator.feed_limit_too_high',
            ],
        ];
    }

    /**
     * @dataProvider dataForFeedFailed
     */
    public function testFeedFailed($data, $expectedMessage)
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/config');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=feed_config_save]')->form();

        $crawler = $client->submit($form, $data);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(1, $alert = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString($expectedMessage, $alert[0]);
    }

    public function testTaggingRuleCreation()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/config');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=tagging_rule_save]')->form();

        $data = [
            'tagging_rule[rule]' => 'readingTime <= 3',
            'tagging_rule[tags]' => 'short reading',
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('flashes.config.notice.tagging_rules_updated', $crawler->filter('body')->extract(['_text'])[0]);

        $editLink = $crawler->filter('.mode_edit_tagging_rule')->last()->link();

        $crawler = $client->click($editLink);
        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('?tagging-rule=', $client->getResponse()->headers->get('location'));

        $crawler = $client->followRedirect();

        $form = $crawler->filter('button[id=tagging_rule_save]')->form();

        $data = [
            'tagging_rule[rule]' => 'readingTime <= 30',
            'tagging_rule[tags]' => 'short reading',
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('flashes.config.notice.tagging_rules_updated', $crawler->filter('body')->extract(['_text'])[0]);

        $this->assertStringContainsString('readingTime <= 30', $crawler->filter('body')->extract(['_text'])[0]);

        $crawler = $client->submit($crawler->filter('#set5')->selectButton('delete')->form());

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();
        $this->assertStringContainsString('flashes.config.notice.tagging_rules_deleted', $crawler->filter('body')->extract(['_text'])[0]);
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
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/config');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=tagging_rule_save]')->form();

        $crawler = $client->submit($form, $data);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));

        foreach ($messages as $message) {
            $this->assertStringContainsString($message, $body[0]);
        }
    }

    public function testTaggingRuleTooLong()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/config');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=tagging_rule_save]')->form();

        $crawler = $client->submit($form, [
            'tagging_rule[rule]' => str_repeat('title', 60),
            'tagging_rule[tags]' => 'cool tag',
        ]);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));

        $this->assertStringContainsString('255 characters', $body[0]);
    }

    public function testDeletingTaggingRuleFromAnOtherUser()
    {
        $this->logInAs('bob');
        $client = $this->getTestClient();

        $rule = $client->getContainer()->get(EntityManagerInterface::class)
            ->getRepository(TaggingRule::class)
            ->findAll()[0];

        $crawler = $client->request('POST', '/tagging-rule/delete/' . $rule->getId());

        $this->assertSame(404, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('404: Not Found', $body[0]);
    }

    public function testEditingTaggingRuleFromAnOtherUser()
    {
        $this->logInAs('bob');
        $client = $this->getTestClient();

        $rule = $client->getContainer()->get(EntityManagerInterface::class)
            ->getRepository(TaggingRule::class)
            ->findAll()[0];

        $crawler = $client->request('GET', '/tagging-rule/edit/' . $rule->getId());

        $this->assertSame(404, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('404: Not Found', $body[0]);
    }

    public function testIgnoreOriginRuleCreation()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/config');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=ignore_origin_user_rule_save]')->form();

        $data = [
            'ignore_origin_user_rule[rule]' => 'host = "example.com"',
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('flashes.config.notice.ignore_origin_rules_updated', $crawler->filter('body')->extract(['_text'])[0]);

        $editLink = $crawler->filter('div[id=set6] a.mode_edit')->last()->link();

        $crawler = $client->click($editLink);
        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('?ignore-origin-user-rule=', $client->getResponse()->headers->get('location'));

        $crawler = $client->followRedirect();

        $form = $crawler->filter('button[id=ignore_origin_user_rule_save]')->form();

        $data = [
            'ignore_origin_user_rule[rule]' => 'host = "example.org"',
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('flashes.config.notice.ignore_origin_rules_updated', $crawler->filter('body')->extract(['_text'])[0]);

        $this->assertStringContainsString('host = "example.org"', $crawler->filter('body')->extract(['_text'])[0]);

        $form = $crawler->filter('#set6')->selectButton('delete')->form();

        $crawler = $client->submit($form);
        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();
        $this->assertStringContainsString('flashes.config.notice.ignore_origin_rules_deleted', $crawler->filter('body')->extract(['_text'])[0]);
    }

    public function dataForIgnoreOriginRuleCreationFail()
    {
        return [
            [
                [
                    'ignore_origin_user_rule[rule]' => 'foo = "bar"',
                ],
                [
                    'The variable',
                    'does not exist.',
                ],
            ],
            [
                [
                    'ignore_origin_user_rule[rule]' => '_all != "none"',
                ],
                [
                    'The operator',
                    'does not exist.',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataForIgnoreOriginRuleCreationFail
     */
    public function testIgnoreOriginRuleCreationFail($data, $messages)
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/config');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=ignore_origin_user_rule_save]')->form();

        $crawler = $client->submit($form, $data);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));

        foreach ($messages as $message) {
            $this->assertStringContainsString($message, $body[0]);
        }
    }

    public function testDeletingIgnoreOriginRuleFromAnOtherUser()
    {
        $this->logInAs('bob');
        $client = $this->getTestClient();

        $rule = $client->getContainer()->get(EntityManagerInterface::class)
            ->getRepository(IgnoreOriginUserRule::class)
            ->findAll()[0];

        $crawler = $client->request('POST', '/ignore-origin-user-rule/delete/' . $rule->getId());

        $this->assertSame(404, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('404: Not Found', $body[0]);
    }

    public function testEditingIgnoreOriginRuleFromAnOtherUser()
    {
        $this->logInAs('bob');
        $client = $this->getTestClient();

        $rule = $client->getContainer()->get(EntityManagerInterface::class)
            ->getRepository(IgnoreOriginUserRule::class)
            ->findAll()[0];

        $crawler = $client->request('GET', '/ignore-origin-user-rule/edit/' . $rule->getId());

        $this->assertSame(404, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('404: Not Found', $body[0]);
    }

    public function testDeleteUserButtonVisibility()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/config');

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('config.form_user.delete.button', $body[0]);

        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('empty');
        $user->setEnabled(false);
        $em->persist($user);

        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('bob');
        $user->setEnabled(false);
        $em->persist($user);

        $em->flush();

        $crawler = $client->request('GET', '/config');

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringNotContainsString('config.form_user.delete.button', $body[0]);

        $client->request('POST', '/account/delete');
        $this->assertSame(400, $client->getResponse()->getStatusCode());

        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('empty');
        $user->setEnabled(true);
        $em->persist($user);

        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('bob');
        $user->setEnabled(true);
        $em->persist($user);

        $em->flush();
    }

    /**
     * @group NetworkCalls
     */
    public function testDeleteAccount()
    {
        $client = $this->getTestClient();
        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $user = new User();
        $user->setName('Wallace');
        $user->setEmail('wallace@wallabag.org');
        $user->setUsername('wallace');
        $user->setPlainPassword('wallace');
        $user->setEnabled(true);
        $user->addRole('ROLE_SUPER_ADMIN');

        $em->persist($user);

        $config = new ConfigEntity($user);

        $config->setItemsPerPage(30);
        $config->setReadingSpeed(200);
        $config->setLanguage('en');
        $config->setPocketConsumerKey('xxxxx');

        $em->persist($config);
        $em->flush();

        $this->logInAs('wallace');
        $loggedInUserId = $this->getLoggedInUserId();

        // create entry to check after user deletion
        // that this entry is also deleted
        $crawler = $client->request('GET', '/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=entry]')->form();
        $data = [
            'entry[url]' => $url = 'https://github.com/wallabag/wallabag',
        ];

        $client->submit($form, $data);
        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->request('GET', '/config');

        $deleteForm = $crawler->filter('form[name=delete-account]')->form();

        $client->submit($deleteForm);
        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $user = $em
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.username = :username')->setParameter('username', 'wallace')
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $this->assertNull($user);

        $entries = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUser($loggedInUserId);

        $this->assertEmpty($entries);
    }

    public function testReset()
    {
        $this->logInAs('empty');
        $client = $this->getTestClient();

        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $user = static::$kernel->getContainer()->get(TokenStorageInterface::class)->getToken()->getUser();

        \assert($user instanceof User);

        $tag = new Tag();
        $tag->setLabel('super');
        $em->persist($tag);

        $taggingRule = new TaggingRule();
        $taggingRule->setConfig($user->getConfig());
        $taggingRule->setRule('readingTime <= 30');
        $taggingRule->setTags(['aTestTag']);
        $em->persist($taggingRule);

        $entry = new Entry($user);
        $entry->setUrl('https://www.lemonde.fr/europe/article/2016/10/01/pour-le-psoe-chaque-election-s-est-transformee-en-une-agonie_5006476_3214.html');
        $entry->setContent('Youhou');
        $entry->setTitle('Youhou');
        $entry->addTag($tag);
        $em->persist($entry);

        $entry2 = new Entry($user);
        $entry2->setUrl('http://www.lemonde.de/europe/article/2016/10/01/pour-le-psoe-chaque-election-s-est-transformee-en-une-agonie_5006476_3214.html');
        $entry2->setContent('Youhou');
        $entry2->setTitle('Youhou');
        $entry2->addTag($tag);
        $em->persist($entry2);

        $annotation = new Annotation($user);
        $annotation->setText('annotated');
        $annotation->setQuote('annotated');
        $annotation->setRanges([]);
        $annotation->setEntry($entry);
        $em->persist($annotation);

        $em->flush();

        // reset annotations
        $crawler = $client->request('GET', '/config#set3');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=reset-annotations]')->form();
        $client->submit($form);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('flashes.config.notice.annotations_reset', $client->getContainer()->get(SessionInterface::class)->getFlashBag()->get('notice')[0]);

        $annotationsReset = $em
            ->getRepository(Annotation::class)
            ->findByEntryIdAndUserId($entry->getId(), $user->getId());

        $this->assertEmpty($annotationsReset, 'Annotations were reset');

        // reset taggingRules
        $crawler = $client->request('GET', '/config#set3');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=reset-tagging-rules]')->form();
        $client->submit($form);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('flashes.config.notice.tagging_rules_reset', $client->getContainer()->get(SessionInterface::class)->getFlashBag()->get('notice')[0]);

        $taggingRuleReset = $em
            ->getRepository(TaggingRule::class)
            ->count(['config' => $user->getConfig()]);

        $this->assertSame(0, $taggingRuleReset, 'Tagging rules were reset');

        // reset tags
        $crawler = $client->request('GET', '/config#set3');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=reset-tags]')->form();
        $client->submit($form);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('flashes.config.notice.tags_reset', $client->getContainer()->get(SessionInterface::class)->getFlashBag()->get('notice')[0]);

        $tagReset = $em
            ->getRepository(Tag::class)
            ->countAllTags($user->getId());

        $this->assertSame(0, $tagReset, 'Tags were reset');

        // reset entries
        $crawler = $client->request('GET', '/config#set3');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=reset-entries]')->form();
        $client->submit($form);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('flashes.config.notice.entries_reset', $client->getContainer()->get(SessionInterface::class)->getFlashBag()->get('notice')[0]);

        $entryReset = $em
            ->getRepository(Entry::class)
            ->countAllEntriesByUser($user->getId());

        $this->assertSame(0, $entryReset, 'Entries were reset');
    }

    public function testResetArchivedEntries()
    {
        $this->logInAs('empty');
        $client = $this->getTestClient();

        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $user = static::$kernel->getContainer()->get(TokenStorageInterface::class)->getToken()->getUser();

        \assert($user instanceof User);

        $tag = new Tag();
        $tag->setLabel('super');
        $em->persist($tag);

        $entry = new Entry($user);
        $entry->setUrl('https://www.lemonde.fr/europe/article/2016/10/01/pour-le-psoe-chaque-election-s-est-transformee-en-une-agonie_5006476_3214.html');
        $entry->setContent('Youhou');
        $entry->setTitle('Youhou');
        $entry->addTag($tag);
        $em->persist($entry);

        $annotation = new Annotation($user);
        $annotation->setText('annotated');
        $annotation->setQuote('annotated');
        $annotation->setRanges([]);
        $annotation->setEntry($entry);
        $em->persist($annotation);

        $tagArchived = new Tag();
        $tagArchived->setLabel('super');
        $em->persist($tagArchived);

        $entryArchived = new Entry($user);
        $entryArchived->setUrl('https://www.lemonde.fr/europe/article/2016/10/01/pour-le-psoe-chaque-election-s-est-transformee-en-une-agonie_5006476_3214.html');
        $entryArchived->setContent('Youhou');
        $entryArchived->setTitle('Youhou');
        $entryArchived->addTag($tagArchived);
        $entryArchived->updateArchived(true);
        $em->persist($entryArchived);

        $annotationArchived = new Annotation($user);
        $annotationArchived->setText('annotated');
        $annotationArchived->setQuote('annotated');
        $annotationArchived->setRanges([]);
        $annotationArchived->setEntry($entryArchived);
        $em->persist($annotationArchived);

        $em->flush();

        $crawler = $client->request('GET', '/config#set3');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=reset-archived]')->form();
        $client->submit($form);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('flashes.config.notice.archived_reset', $client->getContainer()->get(SessionInterface::class)->getFlashBag()->get('notice')[0]);

        $entryReset = $em
            ->getRepository(Entry::class)
            ->countAllEntriesByUser($user->getId());

        $this->assertSame(1, $entryReset, 'Entries were reset');

        $tagReset = $em
            ->getRepository(Tag::class)
            ->countAllTags($user->getId());

        $this->assertSame(1, $tagReset, 'Tags were reset');

        $annotationsReset = $em
            ->getRepository(Annotation::class)
            ->findByEntryIdAndUserId($annotationArchived->getId(), $user->getId());

        $this->assertEmpty($annotationsReset, 'Annotations were reset');
    }

    public function testResetEntriesCascade()
    {
        $this->logInAs('empty');
        $client = $this->getTestClient();

        $em = $client->getContainer()->get(EntityManagerInterface::class);

        $user = static::$kernel->getContainer()->get(TokenStorageInterface::class)->getToken()->getUser();

        \assert($user instanceof User);

        $tag = new Tag();
        $tag->setLabel('super');
        $em->persist($tag);

        $entry = new Entry($user);
        $entry->setUrl('https://www.lemonde.fr/europe/article/2016/10/01/pour-le-psoe-chaque-election-s-est-transformee-en-une-agonie_5006476_3214.html');
        $entry->setContent('Youhou');
        $entry->setTitle('Youhou');
        $entry->addTag($tag);
        $em->persist($entry);

        $annotation = new Annotation($user);
        $annotation->setText('annotated');
        $annotation->setQuote('annotated');
        $annotation->setRanges([]);
        $annotation->setEntry($entry);
        $em->persist($annotation);

        $em->flush();

        $crawler = $client->request('GET', '/config#set3');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=reset-entries]')->form();
        $client->submit($form);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('flashes.config.notice.entries_reset', $client->getContainer()->get(SessionInterface::class)->getFlashBag()->get('notice')[0]);

        $entryReset = $em
            ->getRepository(Entry::class)
            ->countAllEntriesByUser($user->getId());

        $this->assertSame(0, $entryReset, 'Entries were reset');

        $tagReset = $em
            ->getRepository(Tag::class)
            ->countAllTags($user->getId());

        $this->assertSame(0, $tagReset, 'Tags were reset');

        $annotationsReset = $em
            ->getRepository(Annotation::class)
            ->findByEntryIdAndUserId($entry->getId(), $user->getId());

        $this->assertEmpty($annotationsReset, 'Annotations were reset');
    }

    public function testSwitchViewMode()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/unread/list');

        $this->assertStringContainsString('row data', $client->getResponse()->getContent());

        $form = $crawler->filter('.nb-results')->selectButton('view_list')->form();

        $client->submit($form);

        $client->followRedirect();

        $this->assertStringContainsString('collection', $client->getResponse()->getContent());
    }

    public function testChangeLocaleWithoutReferer()
    {
        $client = $this->getTestClient();

        $crawler = $client->request('POST', '/locale/de');

        $this->assertSame(400, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('Bad CSRF token.', $body[0]);
    }

    public function testChangeLocaleWithReferer()
    {
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/login');

        $client->submit($crawler->selectButton('Deutsch')->form());

        $client->followRedirect();

        $this->assertSame('de', $client->getRequest()->getLocale());
        $this->assertSame('de', $client->getContainer()->get(SessionInterface::class)->get('_locale'));
    }

    public function testChangeLocaleToBadLocale()
    {
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/login');
        $token = $crawler->filter('form[action="/locale/de"] input[name=token]')->attr('value');

        $client->request('POST', '/locale/yuyuyuyu', [
            'token' => $token,
        ]);
        $client->followRedirect();

        $this->assertNotSame('yuyuyuyu', $client->getRequest()->getLocale());
        $this->assertNotSame('yuyuyuyu', $client->getContainer()->get(SessionInterface::class)->get('_locale'));
    }

    public function testUserEnable2faEmail()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/config');

        $form = $crawler->filter('form[name=config_otp_email]')->form();
        $client->submit($form);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('flashes.config.notice.otp_enabled', $client->getContainer()->get(SessionInterface::class)->getFlashBag()->get('notice')[0]);

        // restore user
        $em = $this->getEntityManager();
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');

        $this->assertTrue($user->isEmailTwoFactor());

        $user->setEmailTwoFactor(false);
        $em->persist($user);
        $em->flush();
    }

    public function testUserDisable2faEmail()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $em = $this->getEntityManager();
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');

        $user->setEmailTwoFactor(true);
        $em->persist($user);
        $em->flush();

        $crawler = $client->request('GET', '/config');

        $form = $crawler->filter('form[name=disable_otp_email]')->form();
        $client->submit($form);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $this->assertStringContainsString('flashes.config.notice.otp_disabled', $client->getContainer()->get(SessionInterface::class)->getFlashBag()->get('notice')[0]);

        // restore user
        $em = $this->getEntityManager();
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');

        $this->assertFalse($user->isEmailTwoFactor());
    }

    public function testUserEnable2faGoogle()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/config');

        $form = $crawler->filter('form[name=config_otp_app]')->form();
        $client->submit($form);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        // restore user
        $em = $this->getEntityManager();
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');

        $this->assertTrue($user->isGoogleTwoFactor());
        $this->assertGreaterThan(0, $user->getBackupCodes());

        $user->setGoogleAuthenticatorSecret(false);
        $user->setBackupCodes(null);
        $em->persist($user);
        $em->flush();
    }

    public function testUserDisable2faGoogle()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $em = $this->getEntityManager();
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');

        $user->setGoogleAuthenticatorSecret('Google2FA');
        $em->persist($user);
        $em->flush();

        $crawler = $client->request('GET', '/config');

        $form = $crawler->filter('form[name=disable_otp_app]')->form();
        $client->submit($form);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $this->assertStringContainsString('flashes.config.notice.otp_disabled', $client->getContainer()->get(SessionInterface::class)->getFlashBag()->get('notice')[0]);

        // restore user
        $em = $this->getEntityManager();
        $user = $em
            ->getRepository(User::class)
            ->findOneByUsername('admin');

        $this->assertEmpty($user->getGoogleAuthenticatorSecret());
        $this->assertEmpty($user->getBackupCodes());
    }

    public function testExportTaggingRule()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        ob_start();
        $crawler = $client->request('GET', '/tagging-rule/export');
        ob_end_clean();

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $headers = $client->getResponse()->headers;
        $this->assertSame('application/json', $headers->get('content-type'));
        $this->assertSame('attachment; filename="tagging_rules_admin.json"', $headers->get('content-disposition'));
        $this->assertSame('UTF-8', $headers->get('content-transfer-encoding'));

        $content = json_decode((string) $client->getResponse()->getContent(), true);

        $this->assertCount(4, $content);
        $this->assertSame('content matches "spurs"', $content[0]['rule']);
        $this->assertSame('sport', $content[0]['tags'][0]);
    }

    public function testImportTagginfRuleBadFile()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/config');
        $form = $crawler->filter('form[name=upload_tagging_rule_file] > button[type=submit]')->form();

        $data = [
            'upload_tagging_rule_file[file]' => '',
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());
    }

    public function testImportTagginfRuleFile()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/config');
        $form = $crawler->filter('form[name=upload_tagging_rule_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../fixtures/tagging_rules_admin.json', 'tagging_rules_admin.json');

        $data = [
            'upload_tagging_rule_file[file]' => $file,
        ];

        $client->submit($form, $data);
        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $user = static::getContainer()->get('fos_user.user_manager')->findUserBy(['username' => 'admin']);
        \assert($user instanceof User);
        $taggingRules = $user->getConfig()->getTaggingRules()->toArray();
        $this->assertCount(5, $taggingRules);
        $this->assertSame('title matches "football"', $taggingRules[4]->getRule());
    }

    public function testSwitchDisplayThumbnails()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        // Change configuration to show thumbnails
        $crawler = $client->request('GET', '/config');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $form = $crawler->filter('button[id=config_save]')->form();
        $data = [
            'config[display_thumbnails]' => true,
        ];
        $client->submit($form, $data);
        $client->followRedirect();

        $client->request('GET', '/unread/list');

        $this->assertStringContainsString('class="preview"', $client->getResponse()->getContent());

        // Change configuration to hide thumbnails
        $crawler = $client->request('GET', '/config');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $form = $crawler->filter('button[id=config_save]')->form();
        $data = [
            'config[display_thumbnails]' => false,
        ];
        $client->submit($form, $data);
        $client->followRedirect();

        $client->request('GET', '/unread/list');

        $this->assertStringNotContainsString('class="preview"', $client->getResponse()->getContent());
    }

    public function testGeneratedCSS()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        // We check the current display
        $em = $client->getContainer()->get(EntityManagerInterface::class);
        $entry = $em->getRepository(Entry::class)->findByUrlAndUserId('http://0.0.0.0/entry1', $this->getLoggedInUserId());
        $client->request('GET', '/view/' . $entry->getId());

        $this->assertStringNotContainsString('font-family: "OpenDyslexicRegular"', $client->getResponse()->getContent());
        $this->assertStringNotContainsString('max-width: 60em', $client->getResponse()->getContent());
        $this->assertStringNotContainsString('line-height: 2em', $client->getResponse()->getContent());
        $this->assertStringNotContainsString('font-size: 2em', $client->getResponse()->getContent());

        // Change display configuration
        $crawler = $client->request('GET', '/config');
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $form = $crawler->filter('button[id=config_save]')->form();
        $data = [
            'config[font]' => 'OpenDyslexicRegular',
            'config[fontsize]' => 2.0,
            'config[lineHeight]' => 2.0,
            'config[maxWidth]' => 60,
        ];
        $client->submit($form, $data);
        $client->followRedirect();

        $client->request('GET', '/view/' . $entry->getId());

        $this->assertStringContainsString('font-family: "OpenDyslexicRegular"', $client->getResponse()->getContent());
        $this->assertStringContainsString('max-width: 60em', $client->getResponse()->getContent());
        $this->assertStringContainsString('line-height: 2em', $client->getResponse()->getContent());
        $this->assertStringContainsString('font-size: 2em', $client->getResponse()->getContent());
    }
}
