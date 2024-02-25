<?php

namespace Tests\Wallabag\Controller\Import;

use Craue\ConfigBundle\Util\Config;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\Entry;

class WallabagV2ControllerTest extends WallabagTestCase
{
    public function testImportWallabag()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/wallabag-v2');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());
    }

    public function testImportWallabagWithRabbitEnabled()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->getContainer()->get(Config::class)->set('import_with_rabbitmq', 1);

        $crawler = $client->request('GET', '/import/wallabag-v2');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());

        $client->getContainer()->get(Config::class)->set('import_with_rabbitmq', 0);
    }

    public function testImportWallabagBadFile()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/wallabag-v2');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $data = [
            'upload_import_file[file]' => '',
        ];

        $client->submit($form, $data);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testImportWallabagWithRedisEnabled()
    {
        $this->checkRedis();
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->getContainer()->get(Config::class)->set('import_with_redis', 1);

        $crawler = $client->request('GET', '/import/wallabag-v2');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());

        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../fixtures/Import/wallabag-v2.json', 'wallabag-v2.json');

        $data = [
            'upload_import_file[file]' => $file,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.summary', $body[0]);

        $this->assertNotEmpty($client->getContainer()->get(Client::class)->lpop('wallabag.import.wallabag_v2'));

        $client->getContainer()->get(Config::class)->set('import_with_redis', 0);
    }

    public function testImportWallabagWithFile()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/wallabag-v2');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../fixtures/Import/wallabag-v2.json', 'wallabag-v2.json');

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
                'https://www.liberation.fr/planete/2015/10/26/refugies-l-ue-va-creer-100-000-places-d-accueil-dans-les-balkans_1408867',
                $this->getLoggedInUserId()
            );

        $this->assertInstanceOf(Entry::class, $content);

        // empty because it wasn't re-imported
        $this->assertEmpty($content->getMimetype(), 'Mimetype for https://www.liberation.fr is empty');
        $this->assertEmpty($content->getPreviewPicture(), 'Preview picture for https://www.liberation.fr is empty');
        $this->assertEmpty($content->getLanguage(), 'Language for https://www.liberation.fr is empty');

        $tags = $content->getTagsLabel();
        $this->assertContains('foot', $tags, 'It includes the "foot" tag');
        $this->assertCount(1, $tags);

        $content = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findByUrlAndUserId(
                'https://www.mediapart.fr/',
                $this->getLoggedInUserId()
            );

        $this->assertInstanceOf(Entry::class, $content);
        $this->assertNotEmpty($content->getMimetype(), 'Mimetype for https://www.mediapart.fr is ok');
        $this->assertNotEmpty($content->getPreviewPicture(), 'Preview picture for https://www.mediapart.fr is ok');
        $this->assertNotEmpty($content->getLanguage(), 'Language for https://www.mediapart.fr is ok');

        $tags = $content->getTagsLabel();
        $this->assertContains('foot', $tags, 'It includes the "foot" tag');
        $this->assertContains('mediapart', $tags, 'It includes the "mediapart" tag');
        $this->assertContains('blog', $tags, 'It includes the "blog" tag');
        $this->assertCount(3, $tags);

        $this->assertInstanceOf(\DateTime::class, $content->getCreatedAt());
        $this->assertSame('2016-09-08', $content->getCreatedAt()->format('Y-m-d'));
        $this->assertTrue($content->isStarred(), 'Entry is starred');
    }

    public function testImportWallabagWithEmptyFile()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/wallabag-v2');
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
