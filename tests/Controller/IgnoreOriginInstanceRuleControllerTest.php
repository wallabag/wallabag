<?php

namespace Tests\Wallabag\Controller;

use Tests\Wallabag\WallabagTestCase;

class IgnoreOriginInstanceRuleControllerTest extends WallabagTestCase
{
    public function testListIgnoreOriginInstanceRule()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/ignore-origin-instance-rules');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $body = $crawler->filter('body')->extract(['_text'])[0];

        $this->assertStringContainsString('ignore_origin_instance_rule.description', $body);
        $this->assertStringContainsString('ignore_origin_instance_rule.list.create_new_one', $body);
    }

    public function testIgnoreOriginInstanceRuleCreationEditionDeletion()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        // Creation
        $crawler = $client->request('GET', '/ignore-origin-instance-rules/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $body = $crawler->filter('body')->extract(['_text'])[0];

        $this->assertStringContainsString('ignore_origin_instance_rule.new_ignore_origin_instance_rule', $body);
        $this->assertStringContainsString('ignore_origin_instance_rule.form.back_to_list', $body);

        $form = $crawler->filter('button[id=ignore_origin_instance_rule_save]')->form();

        $data = [
            'ignore_origin_instance_rule[rule]' => 'host = "foo.example.com"',
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('flashes.ignore_origin_instance_rule.notice.added', $crawler->filter('body')->extract(['_text'])[0]);

        // Edition
        $editLink = $crawler->filter('div[id=content] table a')->last()->link();

        $crawler = $client->click($editLink);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertStringContainsString('foo.example.com', $crawler->filter('form[name=ignore_origin_instance_rule] input[type=text]')->extract(['value'])[0]);

        $body = $crawler->filter('body')->extract(['_text'])[0];

        $this->assertStringContainsString('ignore_origin_instance_rule.edit_ignore_origin_instance_rule', $body);
        $this->assertStringContainsString('ignore_origin_instance_rule.form.back_to_list', $body);

        $form = $crawler->filter('button[id=ignore_origin_instance_rule_save]')->form();

        $data = [
            'ignore_origin_instance_rule[rule]' => 'host = "bar.example.com"',
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('flashes.ignore_origin_instance_rule.notice.updated', $crawler->filter('body')->extract(['_text'])[0]);

        $editLink = $crawler->filter('div[id=content] table a')->last()->link();

        $crawler = $client->click($editLink);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertStringContainsString('bar.example.com', $crawler->filter('form[name=ignore_origin_instance_rule] input[type=text]')->extract(['value'])[0]);

        $deleteForm = $crawler->filter('body')->selectButton('ignore_origin_instance_rule.form.delete')->form();

        $client->submit($deleteForm, []);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('flashes.ignore_origin_instance_rule.notice.deleted', $crawler->filter('body')->extract(['_text'])[0]);
    }

    public function dataForIgnoreOriginInstanceRuleCreationFail()
    {
        return [
            [
                [
                    'ignore_origin_instance_rule[rule]' => 'foo = "bar"',
                ],
                [
                    'The variable',
                    'does not exist.',
                ],
            ],
            [
                [
                    'ignore_origin_instance_rule[rule]' => '_all != "none"',
                ],
                [
                    'The operator',
                    'does not exist.',
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataForIgnoreOriginInstanceRuleCreationFail
     */
    public function testIgnoreOriginInstanceRuleCreationFail($data, $messages)
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/ignore-origin-instance-rules/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('button[id=ignore_origin_instance_rule_save]')->form();

        $crawler = $client->submit($form, $data);

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));

        foreach ($messages as $message) {
            $this->assertStringContainsString($message, $body[0]);
        }
    }
}
