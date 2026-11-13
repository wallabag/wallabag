<?php

namespace Wallabag\Tests\Functional\Controller\Import;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Wallabag\Tests\Functional\WallabagTestCase;

/**
 * Uploaded import files must never stay on disk, whatever the import result is,
 * and must never be stored in a directory served by the web server.
 */
class UploadedFileCleanupTest extends WallabagTestCase
{
    public static function dataForEachFileImport(): array
    {
        return [
            'chrome' => ['/import/chrome', 'test.txt'],
            'firefox' => ['/import/firefox', 'test.txt'],
            'delicious' => ['/import/delicious', 'test.txt'],
            'elcurator' => ['/import/elcurator', 'test.txt'],
            'instapaper' => ['/import/instapaper', 'test.txt'],
            'omnivore' => ['/import/omnivore', 'test.txt'],
            'pinboard' => ['/import/pinboard', 'test.txt'],
            'pocket_csv' => ['/import/pocket_csv', 'test.txt'],
            'pocket_html' => ['/import/pocket_html', 'test.html'],
            'readability' => ['/import/readability', 'test.txt'],
            'shaarli' => ['/import/shaarli', 'test.html'],
            'wallabag_v1' => ['/import/wallabag-v1', 'test.txt'],
            'wallabag_v2' => ['/import/wallabag-v2', 'test.txt'],
        ];
    }

    /**
     * @dataProvider dataForEachFileImport
     */
    public function testFailedImportDoesNotKeepTheUploadedFile(string $url, string $fixture): void
    {
        $this->logInAs('admin');
        $client = $this->getTestClient();

        $resourceDir = $client->getContainer()->getParameter('wallabag.resource_dir');

        // the upload directory must never be reachable from the web root
        $this->assertStringNotContainsString('/web/', $resourceDir);

        $crawler = $client->request('GET', $url);
        $form = $crawler->filter('form[name=upload_import_file] > button[type=submit]')->form();

        $file = new UploadedFile(__DIR__ . '/../../../fixtures/Import/' . $fixture, $fixture);

        $client->submit($form, ['upload_import_file[file]' => $file]);

        $this->assertSame(302, $client->getResponse()->getStatusCode());

        $crawler = $client->followRedirect();
        $this->assertStringContainsString('flashes.import.notice.failed', $crawler->filter('body')->text());

        $this->assertSame([], glob($resourceDir . '/*'));
    }
}
