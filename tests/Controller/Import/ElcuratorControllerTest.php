<?php

namespace Tests\Wallabag\Controller\Import;

use Craue\ConfigBundle\Util\Config;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\Entry;

class ElcuratorControllerTest extends WallabagTestCase
{
    public function testImportElcurator()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/elcurator');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());
    }

    public function testImportElcuratorWithRabbitEnabled()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->getContainer()->get(Config::class)->set('import_with_rabbitmq', 1);

        $crawler = $client->request('GET', '/import/elcurator');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());

        $client->getContainer()->get(Config::class)->set('import_with_rabbitmq', 0);
    }

    public function testImportElcuratorBadFile()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/elcurator');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $data = [
            'upload_import_file[file]' => '',
        ];

        $client->submit($form, $data);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testImportElcuratorWithRedisEnabled()
    {
        $this->checkRedis();
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->getContainer()->get(Config::class)->set('import_with_redis', 1);

        $crawler = $client->request('GET', '/import/elcurator');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());

        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../fixtures/Import/elcurator.json', 'elcurator.json');

        $data = [
            'upload_import_file[file]' => $file,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.summary', $body[0]);

        $this->assertNotEmpty($client->getContainer()->get(Client::class)->lpop('wallabag.import.elcurator'));

        $client->getContainer()->get(Config::class)->set('import_with_redis', 0);
    }

    public function testImportElcuratorWithFile()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/elcurator');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../fixtures/Import/elcurator.json', 'elcurator.json');

        $data = [
            'upload_import_file[file]' => $file,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.summary', $body[0]);

        $content = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId(
                'https://devblog.lexik.fr/git/qualite-de-code-integration-de-php-git-hooks-dans-symfony2-2842',
                $this->getLoggedInUserId()
            );

        $this->assertInstanceOf(Entry::class, $content);

        $this->assertStringContainsString('Qualité de code - Intégration de php-git-hooks dans Symfony2', $content->getTitle());
        $this->assertSame('2015-09-09', $content->getCreatedAt()->format('Y-m-d'));
        $this->assertTrue($content->isStarred(), 'Entry is starred');

        $tags = $content->getTagsLabel();
        $this->assertContains('tag1', $tags, 'It includes the "tag1" tag');
        $this->assertContains('tag2', $tags, 'It includes the "tag2" tag');
    }
}
