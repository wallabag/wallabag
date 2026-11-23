<?php

namespace Wallabag\Tests\Functional\Controller\Import;

use Craue\ConfigBundle\Util\Config;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Wallabag\Entity\Entry;
use Wallabag\Tests\Functional\WallabagTestCase;

class UrlListControllerTest extends WallabagTestCase
{
    public function testImportUrlList(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/url_list');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());
    }

    public function testImportUrlListWithRabbitEnabled(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->getContainer()->get(Config::class)->set('import_with_rabbitmq', 1);

        $crawler = $client->request('GET', '/import/url_list');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());

        $client->getContainer()->get(Config::class)->set('import_with_rabbitmq', 0);
    }

    public function testImportUrlListBadFile(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/url_list');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $data = [
            'upload_import_file[file]' => '',
        ];

        $client->submit($form, $data);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testImportUrlListWithRedisEnabled(): void
    {
        $this->checkRedis();
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $client->getContainer()->get(Config::class)->set('import_with_redis', 1);

        $crawler = $client->request('GET', '/import/url_list');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());

        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../../fixtures/Import/url-list.txt', 'url-list.txt');

        $data = [
            'upload_import_file[file]' => $file,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.summary', $body[0]);

        $this->assertNotEmpty($client->getContainer()->get(Client::class)->lpop('wallabag.import.url_list'));

        $client->getContainer()->get(Config::class)->set('import_with_redis', 0);
    }

    public function testImportUrlListWithFile(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/url_list');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../../fixtures/Import/url-list.txt', 'url-list.txt');

        $data = [
            'upload_import_file[file]' => $file,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.summary', $body[0]);

        $entryRepository = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class);

        $expectedUrls = [
            'http://youmightnotneedjquery.com/',
            'https://wallabag.org/',
            'https://reporterre.net/Des-abeilles-pour-resoudre-les-conflits-entre-les-humains-et-les-elephants',
        ];

        foreach ($expectedUrls as $expectedUrl) {
            $entry = $entryRepository->findByUrlAndUserId($expectedUrl, $this->getLoggedInUserId());

            $this->assertInstanceOf(Entry::class, $entry, \sprintf('"%s" should have been imported', $expectedUrl));
            $this->assertFalse($entry->isArchived());
        }

        $this->assertFalse($entryRepository->findByUrlAndUserId('ftp://ftp.example.com/file.txt', $this->getLoggedInUserId()));
    }

    public function testImportUrlListWithFileAndMarkAllAsRead(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/url_list');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../../fixtures/Import/url-list.txt', 'url-list.txt');

        $data = [
            'upload_import_file[file]' => $file,
            'upload_import_file[mark_as_read]' => 1,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $entry = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId(
                'https://wallabag.org/',
                $this->getLoggedInUserId(),
            );

        $this->assertInstanceOf(Entry::class, $entry);
        $this->assertTrue($entry->isArchived());

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.summary', $body[0]);
    }

    public function testImportUrlListWithEmptyFile(): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/url_list');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../../fixtures/Import/test.txt', 'test.txt');

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
