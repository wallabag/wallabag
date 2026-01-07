<?php

namespace Tests\Wallabag\Controller\Import;

use Craue\ConfigBundle\Util\Config;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\Wallabag\WallabagTestCase;
use Wallabag\Entity\Entry;

class PocketCsvControllerTest extends WallabagTestCase
{
    public function testImportPocketCsv()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/pocket_csv');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());
    }

    public function testImportPocketCsvWithRabbitEnabled()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $client->getContainer()->get(Config::class)->set('import_with_rabbitmq', 1);

        $crawler = $client->request('GET', '/import/pocket_csv');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());

        $client->getContainer()->get(Config::class)->set('import_with_rabbitmq', 0);
    }

    public function testImportPocketCsvBadFile()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/pocket_csv');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $data = [
            'upload_import_file[file]' => '',
        ];

        $client->submit($form, $data);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
    }

    public function testImportPocketCsvWithRedisEnabled()
    {
        $this->checkRedis();
        $this->logInAs('admin');
        $client = $this->getTestClient();
        $client->getContainer()->get(Config::class)->set('import_with_redis', 1);

        $crawler = $client->request('GET', '/import/pocket_csv');

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->filter('form[name=upload_import_file] > button[type=submit]')->count());
        $this->assertSame(1, $crawler->filter('input[type=file]')->count());

        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../fixtures/Import/pocket.csv', 'Bookmarks');

        $data = [
            'upload_import_file[file]' => $file,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.summary', $body[0]);

        $this->assertNotEmpty($client->getContainer()->get(Client::class)->lpop('wallabag.import.pocket_csv'));

        $client->getContainer()->get(Config::class)->set('import_with_redis', 0);
    }

    public function testImportWallabagWithPocketCsvFile()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/pocket_csv');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../fixtures/Import/pocket.csv', 'Bookmarks');

        $data = [
            'upload_import_file[file]' => $file,
        ];

        $client->submit($form, $data);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();

        $this->assertGreaterThan(1, $body = $crawler->filter('body')->extract(['_text']));
        $this->assertStringContainsString('flashes.import.notice.summary', $body[0]);

        $entries = $client->getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(Entry::class)
            ->findBy(['user' => $this->getLoggedInUserId()]);

        $expectedEntries = [
            'http://youmightnotneedjquery.com/,1600322788',
            'https://jp-lambert.me/est-ce-que-jai-besoin-d-un-scrum-master-604f5a471c73',
            'https://www.monde-diplomatique.fr/2020/09/HALIMI/62165',
            'https://www.reddit.com/r/DataHoarder/comments/ioupbk/archivebox_question_how_do_i_import_links_from_a/',
            'https://www.numerama.com/politique/646826-tu-vas-pleurer-les-premieres-fois-que-se-passe-t-il-au-sein-du-studio-dubisoft-derriere-trackmania.html#utm_medium=distibuted&utm_source=rss&utm_campaign=646826',
            'https://www.nouvelobs.com/rue89/20200911.OBS33165/comment-konbini-s-est-fait-pieger-par-un-pere-masculiniste.html',
            'https://reporterre.net/Des-abeilles-pour-resoudre-les-conflits-entre-les-humains-et-les-elephants',
        ];

        $matchedEntries = array_map(function ($expectedUrl) use ($entries) {
            foreach ($entries as $entry) {
                if ($entry->getUrl() === $expectedUrl) {
                    return $entry;
                }
            }

            return null;
        }, $expectedEntries);

        $this->assertCount(\count($expectedEntries), $matchedEntries, 'Should have 7 entries imported from pocket.csv');
    }

    public function testImportWallabagWithEmptyFile()
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $crawler = $client->request('GET', '/import/pocket_csv');
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../fixtures/Import/test.csv', 'test.csv');

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
