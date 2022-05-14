<?php

namespace Tests\Wallabag\ImportBundle\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Wallabag\CoreBundle\WallabagCoreTestCase;

class DeliciousControllerTest extends WallabagCoreTestCase
{
    public function testImportDelicious()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/delicious');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());
    }

    public function testImportDeliciousWithRabbitEnabled()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $client->getContainer()->get('craue_config')->set('import_with_rabbitmq', 1);

        $crawler = $client->request('GET', '/import/delicious');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());

        $client->getContainer()->get('craue_config')->set('import_with_rabbitmq', 0);
    }

    public function testImportDeliciousBadFile()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/delicious');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $data = [
            'upload_import_file[file]' => '',
        ];

        $client->submit($form, $data);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testImportDeliciousWithRedisEnabled()
    {
        $this->checkRedis();
        $this->logInAs('admin');
        $client = $this->getClient();
        $client->getContainer()->get('craue_config')->set('import_with_redis', 1);

        $crawler = $client->request('GET', '/import/delicious');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());

        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../fixtures/delicious_export.2021.02.06_21.10.json', 'delicious.json');

        $data = [
            'upload_import_file[file]' => $file,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.summary', $body[0]);

        $this->assertNotEmpty($client->getContainer()->get('wallabag_core.redis.client')->lpop('wallabag.import.delicious'));

        $client->getContainer()->get('craue_config')->set('import_with_redis', 0);
    }

    public function testImportDeliciousWithFile()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/delicious');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../fixtures/delicious_export.2021.02.06_21.10.json', 'delicious.json');

        $data = [
            'upload_import_file[file]' => $file,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $content = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId(
                'https://feross.org/spoofmac/',
                $this->getLoggedInUserId()
            );

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.summary', $body[0]);

        $this->assertInstanceOf('Wallabag\CoreBundle\Entity\Entry', $content);

        $tags = $content->getTagsLabel();
        $this->assertContains('osx', $tags, 'It includes the "osx" tag');
        $this->assertGreaterThanOrEqual(4, \count($tags));

        $this->assertInstanceOf(\DateTime::class, $content->getCreatedAt());
        $this->assertSame('2013-01-17', $content->getCreatedAt()->format('Y-m-d'));
    }

    public function testImportDeliciousWithFileAndMarkAllAsRead()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/delicious');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../fixtures/delicious_export.2021.02.06_21.10.json', 'delicious-read.json');

        $data = [
            'upload_import_file[file]' => $file,
            'upload_import_file[mark_as_read]' => 1,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $content1 = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId(
                'https://stackoverflow.com/review/',
                $this->getLoggedInUserId()
            );

        $this->assertInstanceOf('Wallabag\CoreBundle\Entity\Entry', $content1);

        $content2 = $client->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('WallabagCoreBundle:Entry')
            ->findByUrlAndUserId(
                'https://addyosmani.com/basket.js/',
                $this->getLoggedInUserId()
            );

        $this->assertInstanceOf('Wallabag\CoreBundle\Entity\Entry', $content2);

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.summary', $body[0]);
    }

    public function testImportDeliciousWithEmptyFile()
    {
        $this->logInAs('admin');
        $client = $this->getClient();

        $crawler = $client->request('GET', '/import/delicious');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../fixtures/test.txt', 'test.txt');

        $data = [
            'upload_import_file[file]' => $file,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.failed', $body[0]);
    }
}
