<?php

namespace Wallabag\Tests\Unit\Helper;

use Graby\Graby;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;
use Wallabag\Entity\Config;
use Wallabag\Entity\Entry;
use Wallabag\Helper\ContentProxy;
use Wallabag\Helper\EntriesExport;
use Wallabag\Helper\PublicHttpClient;
use Wallabag\Helper\RuleBasedIgnoreOriginProcessor;
use Wallabag\Helper\RuleBasedTagger;
use Wallabag\Helper\XPostExtractor;
use Wallabag\Entity\User;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class XEntryExportTest extends TestCase
{
    /** @dataProvider providerMetadata */
    public function testProviderMetadataCannotInjectExportResources(bool $article, bool $malicious): void
    {
        // This HTTP trap is reachable only inside the network-none test container.
        [$process, $directory, $resourceUrl] = $this->startResourceTrap();
        try {
            $markup = $malicious ? '<img src="' . $resourceUrl . '" /><script>alert(1)</script>' : '<Friends>';
            $author = 'Renée ' . $markup . ' & O’Connor';
            $title = 'A title ' . $markup . ' & ending';
            $user = new User();
            $config = new Config($user);
            $config->setXArticleReaderEnabled($article);
            $config->setReadingSpeed(200);
            $user->setConfig($config);
            $entry = new Entry($user);
            $http = $this->createMock(PublicHttpClient::class);
            $payload = $article ? ['code' => 200, 'status' => [
                'id' => '12345', 'author' => ['name' => $author, 'protected' => false],
                'article' => ['title' => $title, 'content' => ['blocks' => [['type' => 'unstyled', 'text' => 'Public body.']], 'entityMap' => []]],
            ]] : ['author_name' => $author, 'html' => '<blockquote><p>Public body.</p></blockquote>'];
            $http->expects(self::once())->method('get')->willReturn(['body' => json_encode($payload)]);
            $graby = $this->getMockBuilder(Graby::class)->setMethods(['fetchContent'])->getMock();
            $graby->expects(self::never())->method('fetchContent');
            $proxy = new ContentProxy($graby, $this->createMock(RuleBasedTagger::class), $this->createMock(RuleBasedIgnoreOriginProcessor::class), Validation::createValidator(), new NullLogger(), 'Error', false, new XPostExtractor($http));
            $proxy->updateEntry($entry, 'https://x.com/person/status/12345');
            $entry->setLanguage('en');
            $entry->setCreatedAt(new \DateTime('2026-10-15T12:00:00Z'));
            $title = $entry->getTitle();
            self::assertSame([$author], $entry->getPublishedBy());
            $translator = $this->createMock(TranslatorInterface::class);
            $translator->method('trans')->willReturnCallback(static function ($id) { return $id; });
            $token = $this->createMock(TokenInterface::class);
            $token->method('getUser')->willReturn($user);
            $storage = $this->createMock(TokenStorageInterface::class);
            $storage->method('getToken')->willReturn($token);
            $export = new EntriesExport($translator, 'https://wallabag.example.invalid', '', $storage);
            $export->setEntries($entry)->updateTitle('entry')->updateAuthor('entry');
            $epub = $export->exportAs('epub');
            file_put_contents($directory . '/export.zip', $epub->getContent());
            $zip = new \PharData($directory . '/export.zip');
            $covers = 0;
            try {
                foreach (new \RecursiveIteratorIterator($zip) as $file) {
                    $name = $file->getFilename();
                    if (!preg_match('/\.(html|xhtml|opf|ncx)$/', $name)) {
                        continue;
                    }
                    $doc = new \DOMDocument();
                    $previous = libxml_use_internal_errors(true);
                    try {
                        self::assertTrue($doc->loadXML($file->getContent(), \LIBXML_NONET), $name);
                    } finally {
                        libxml_clear_errors();
                        libxml_use_internal_errors($previous);
                    }
                    $xpath = new \DOMXPath($doc);
                    self::assertSame(0, $xpath->query('//*[local-name()="img" or local-name()="image" or local-name()="svg" or local-name()="script" or local-name()="iframe" or local-name()="object" or (local-name()="source" and namespace-uri()="http://www.w3.org/1999/xhtml")]')->length, $name);
                    if (str_ends_with($name, '.opf')) {
                        self::assertSame($title, $xpath->evaluate('string(//*[local-name()="title"][1])'));
                        self::assertSame($author, $xpath->evaluate('string(//*[local-name()="creator"][1])'));
                    }
                    if (str_ends_with($name, '_cover.html')) {
                        ++$covers;
                        self::assertSame($title, $xpath->evaluate('string(//*[local-name()="h1"][1])'));
                        self::assertSame($author, $xpath->evaluate('string(//*[local-name()="dd"][1])'));
                    }
                }
            } finally {
                unset($zip);
            }
            self::assertSame(1, $covers);
            $pdf = $export->exportAs('pdf');
            self::assertStringStartsWith('%PDF-', $pdf->getContent());
            self::assertNotEmpty($export->exportAs('mobi')->getContent());
            self::assertSame('', file_get_contents($directory . '/requests'), 'Plain metadata must never trigger export image/resource requests.');
            $json = json_decode($export->exportJsonData(), true);
            self::assertSame($title, $json[0]['title']);
            self::assertSame([$author], $json[0]['published_by']);
            self::assertSame($title, $entry->getTitle());
            self::assertSame([$author], $entry->getPublishedBy());
        } finally {
            proc_terminate($process);
            proc_close($process);
            foreach (scandir($directory) as $name) {
                if ('.' !== $name && '..' !== $name) {
                    unlink($directory . '/' . $name);
                }
            }
            rmdir($directory);
        }
    }

    public function providerMetadata(): array
    {
        return ['embed malicious' => [false, true], 'article malicious' => [true, true], 'embed plain' => [false, false], 'article plain' => [true, false]];
    }

    private function startResourceTrap(): array
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
        self::assertIsResource($socket);
        $address = stream_socket_get_name($socket, false);
        fclose($socket);
        $directory = sys_get_temp_dir() . '/wallabag-export-trap-' . bin2hex(random_bytes(6));
        mkdir($directory, 0700);
        file_put_contents($directory . '/requests', '');
        file_put_contents($directory . '/router.php', '<?php file_put_contents(__DIR__."/requests", "1", FILE_APPEND); header("Content-Type: image/png"); echo base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aWQAAAABJRU5ErkJggg==");');
        $process = proc_open([\PHP_BINARY, '-S', $address, $directory . '/router.php'], [0 => ['pipe', 'r'], 1 => ['file', $directory . '/server.log', 'a'], 2 => ['file', $directory . '/server.log', 'a']], $pipes, $directory);
        self::assertIsResource($process);
        fclose($pipes[0]);
        for ($attempt = 0; $attempt < 100; ++$attempt) {
            $connection = @stream_socket_client('tcp://' . $address, $errno, $error, 0.1);
            if ($connection) {
                fclose($connection);

                $url = 'http://' . $address . '/injected.png';
                self::assertNotFalse(file_get_contents($url));
                self::assertSame('1', file_get_contents($directory . '/requests'));
                file_put_contents($directory . '/requests', '');

                return [$process, $directory, $url];
            }
            usleep(10000);
        }
        proc_terminate($process);
        proc_close($process);
        self::fail('Synthetic export resource trap did not become ready.');
    }
}
