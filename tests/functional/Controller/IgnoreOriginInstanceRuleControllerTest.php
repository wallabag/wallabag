<?php

namespace Wallabag\Tests\Functional\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Wallabag\Entity\IgnoreOriginInstanceRule;
use Wallabag\Tests\Functional\WallabagTestCase;

class IgnoreOriginInstanceRuleControllerTest extends WallabagTestCase
{
    public function testListIgnoreOriginInstanceRule(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/ignore-origin-instance-rules');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $body = $crawler->filter('body')->extract(['_text'])[0];

        $this->assertStringContainsString('ignore_origin_instance_rule.description', $body);
        $this->assertStringContainsString('ignore_origin_instance_rule.list.create_new_one', $body);
    }

    public function testIgnoreOriginInstanceRuleCreationEditionDeletion(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $createdHost = 'foo-' . bin2hex(random_bytes(4)) . '.example.com';
        $updatedHost = 'bar-' . bin2hex(random_bytes(4)) . '.example.com';

        // Creation
        $crawler = $client->request('GET', '/ignore-origin-instance-rules/new');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $body = $crawler->filter('body')->extract(['_text'])[0];

        $this->assertStringContainsString('ignore_origin_instance_rule.new_ignore_origin_instance_rule', $body);
        $this->assertStringContainsString('ignore_origin_instance_rule.form.back_to_list', $body);

        $form = $crawler->filter('button[id=ignore_origin_instance_rule_save]')->form();

        $data = [
            'ignore_origin_instance_rule[rule]' => 'host = "' . $createdHost . '"',
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('flashes.ignore_origin_instance_rule.notice.added', $crawler->filter('body')->extract(['_text'])[0]);
        $ignoreOriginInstanceRule = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(IgnoreOriginInstanceRule::class)
            ->findOneBy(['rule' => 'host = "' . $createdHost . '"']);

        \assert($ignoreOriginInstanceRule instanceof IgnoreOriginInstanceRule);

        // Edition
        $crawler = $client->request('GET', '/ignore-origin-instance-rules/' . $ignoreOriginInstanceRule->getId() . '/edit');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertStringContainsString($createdHost, $crawler->filter('form[name=ignore_origin_instance_rule] input[type=text]')->extract(['value'])[0]);

        $body = $crawler->filter('body')->extract(['_text'])[0];

        $this->assertStringContainsString('ignore_origin_instance_rule.edit_ignore_origin_instance_rule', $body);
        $this->assertStringContainsString('ignore_origin_instance_rule.form.back_to_list', $body);

        $form = $crawler->filter('button[id=ignore_origin_instance_rule_save]')->form();

        $data = [
            'ignore_origin_instance_rule[rule]' => 'host = "' . $updatedHost . '"',
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertStringContainsString('flashes.ignore_origin_instance_rule.notice.updated', $crawler->filter('body')->extract(['_text'])[0]);
        $crawler = $client->request('GET', '/ignore-origin-instance-rules/' . $ignoreOriginInstanceRule->getId() . '/edit');

        $this->assertSame(200, $client->getResponse()->getStatusCode());

        $this->assertStringContainsString($updatedHost, $crawler->filter('form[name=ignore_origin_instance_rule] input[type=text]')->extract(['value'])[0]);

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
    public function testIgnoreOriginInstanceRuleCreationFail($data, $messages): void
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
