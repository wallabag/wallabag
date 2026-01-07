<?php

namespace Tests\Wallabag\Controller\Import;

use Craue\ConfigBundle\Util\Config;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\Entry;

class ReadabilityControllerTest extends WallabagTestCase
{
    public function testImportReadability()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/readability');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());
    }

    public function testImportReadabilityWithRabbitEnabled()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->getContainer()->get(Config::class)->set('import_with_rabbitmq', 1);

        $crawler = $client->request('GET', '/import/readability');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());

        $client->getContainer()->get(Config::class)->set('import_with_rabbitmq', 0);
    }

    public function testImportReadabilityBadFile()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/readability');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $data = [
            'upload_import_file[file]' => '',
        ];

        $client->submit($form, $data);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testImportReadabilityWithRedisEnabled()
    {
        $this->checkRedis();
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $client->getContainer()->get(Config::class)->set('import_with_redis', 1);

        $crawler = $client->request('GET', '/import/readability');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());

        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../fixtures/Import/readability.json', 'readability.json');

        $data = [
            'upload_import_file[file]' => $file,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.summary', $body[0]);

        $this->assertNotEmpty($client->getContainer()->get(Client::class)->lpop('wallabag.import.readability'));

        $client->getContainer()->get(Config::class)->set('import_with_redis', 0);
    }

    public function testImportReadabilityWithFile()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/readability');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../fixtures/Import/readability.json', 'readability.json');

        $data = [
            'upload_import_file[file]' => $file,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $content = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId(
                'https://www.20minutes.fr/bordeaux/2120479-20170823-bordeaux-poche-chocolatine-association-traduit-etudiants-etrangers-mots-sud-ouest',
                $this->getLoggedInUserId()
            );

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.summary', $body[0]);

        $this->assertInstanceOf(Entry::class, $content);
        $this->assertNotEmpty($content->getMimetype(), 'Mimetype for https://www.20minutes.fr is ok');
        $this->assertNotEmpty($content->getPreviewPicture(), 'Preview picture for https://www.20minutes.fr is ok');
        $this->assertNotEmpty($content->getLanguage(), 'Language for https://www.20minutes.fr is ok');

        $tags = $content->getTagsLabel();
        $this->assertContains('foot', $tags, 'It includes the "foot" tag');
        $this->assertCount(1, $tags);

        $this->assertInstanceOf(\DateTime::class, $content->getCreatedAt());
        $this->assertSame('2016-09-08', $content->getCreatedAt()->format('Y-m-d'));
    }

    public function testImportReadabilityWithFileAndMarkAllAsRead()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/readability');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../fixtures/Import/readability-read.json', 'readability-read.json');

        $data = [
            'upload_import_file[file]' => $file,
            'upload_import_file[mark_as_read]' => 1,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $content1 = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId(
                'https://blog.travis-ci.com/2016-07-28-what-we-learned-from-analyzing-2-million-travis-builds/',
                $this->getLoggedInUserId()
            );

        $this->assertInstanceOf(Entry::class, $content1);
        $this->assertTrue($content1->isArchived());

        $content2 = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId(
                'https://facebook.github.io/graphql/October2016/',
                $this->getLoggedInUserId()
            );

        $this->assertInstanceOf(Entry::class, $content2);
        $this->assertTrue($content2->isArchived());

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.summary', $body[0]);
    }

    public function testImportReadabilityWithEmptyFile()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/readability');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../fixtures/Import/test.txt', 'test.txt');

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
