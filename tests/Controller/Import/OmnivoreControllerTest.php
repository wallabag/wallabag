<?php

namespace Tests\Wallabag\Controller\Import;

use Craue\ConfigBundle\Util\Config;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\Entry;

class OmnivoreControllerTest extends WallabagTestCase
{
    public function testImportOmnivore()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/omnivore');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());
    }

    public function testImportOmnivoreWithRabbitEnabled()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->getContainer()->get(Config::class)->set('import_with_rabbitmq', 1);

        $crawler = $client->request('GET', '/import/omnivore');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());

        $client->getContainer()->get(Config::class)->set('import_with_rabbitmq', 0);
    }

    public function testImportOmnivoreBadFile()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/omnivore');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $data = [
            'upload_import_file[file]' => '',
        ];

        $client->submit($form, $data);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testImportOmnivoreWithRedisEnabled()
    {
        $this->checkRedis();
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $client->getContainer()->get(Config::class)->set('import_with_redis', 1);

        $crawler = $client->request('GET', '/import/omnivore');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());

        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../fixtures/Import/omnivore.json', 'omnivore.json');

        $data = [
            'upload_import_file[file]' => $file,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.summary', $body[0]);

        $this->assertNotEmpty($client->getContainer()->get(Client::class)->lpop('wallabag.import.omnivore'));

        $client->getContainer()->get(Config::class)->set('import_with_redis', 0);
    }

    public function testImportOmnivoreWithFile()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/omnivore');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../fixtures/Import/omnivore.json', 'omnivore.json');

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
                'https://www.lemonde.fr/economie/article/2024/10/29/malgre-la-crise-du-marche-des-montres-breitling-etend-son-reseau-commercial-et-devoile-ses-ambitions_6365425_3234.html',
                $this->getLoggedInUserId()
            );

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.summary', $body[0]);

        $this->assertInstanceOf(Entry::class, $content);

        $tags = $content->getTagsLabel();
        $this->assertContains('rss', $tags, 'It includes the "rss" tag');
        $this->assertGreaterThanOrEqual(2, \count($tags));

        $this->assertInstanceOf(\DateTime::class, $content->getCreatedAt());
        $this->assertSame('2024-10-29', $content->getCreatedAt()->format('Y-m-d'));
    }

    public function testImportOmnivoreWithFileAndMarkAllAsRead()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/omnivore');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../fixtures/Import/omnivore.json', 'omnivore-read.json');

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
                'https://www.lemonde.fr/economie/article/2024/10/29/l-union-europeenne-adopte-jusqu-a-35-de-surtaxes-sur-les-voitures-electriques-importees-de-chine_6365258_3234.html',
                $this->getLoggedInUserId()
            );

        $this->assertInstanceOf(Entry::class, $content1);

        $content2 = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId(
                'https://www.lemonde.fr/les-decodeurs/article/2024/10/29/presidentielle-americaine-2024-comment-le-calendrier-de-l-election-et-des-affaires-judiciaires-de-trump-s-entremelent_6210916_3211.html',
                $this->getLoggedInUserId()
            );

        $this->assertInstanceOf(Entry::class, $content2);

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.summary', $body[0]);
    }

    public function testImportOmnivoreWithEmptyFile()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/omnivore');
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
